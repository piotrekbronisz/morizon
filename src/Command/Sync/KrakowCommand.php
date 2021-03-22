<?php
namespace App\Command\Sync;

use App\Entity\District;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class KrakowCommand extends Command
{
    private $client;
    private $container;
    private static $url = "https://appimeri.um.krakow.pl/app-pub-dzl/pages/DzlViewGlw.jsf";
    protected static $districtsMaxId = 18;
    protected static $cityId = 2;
    protected static $defaultName = 'sync:krakow';

    public function __construct(HttpClientInterface $client, ContainerInterface $container)
    {
        parent::__construct();
        $this->client = $client;
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription("Getting data about Krakow regions");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("START SYNC");
        $orm = $this->container->get('doctrine');
        $entityManager = $orm->getManager();

        for($i = 1; $i <= self::$districtsMaxId; $i++) {
            $response = $this->client->request(
                'GET',
                self::$url.'?id='.$i
            );
            $string = $response->getContent();
            $stringToArray = explode("\n", $string);

            $districtName = "";
            $area = "";
            $population = "";
            $j=0;
            foreach($stringToArray as $row) {
                if(trim($row) == "<h3>") {
                    $districtName = html_entity_decode(str_replace("&nbsp;", " ", trim($stringToArray[$j+1])));
                }
                if(strstr($row, "Powierzchnia:")) {
                    $area = $stringToArray[$j+2];
                }
                if(strstr($row, "Liczba ludno")) {
                    $population = $stringToArray[$j+1];
                }
                $j++;
            }

            $area = (string)simplexml_load_string($area);
            $area = trim(str_replace(",",".", $area));
            $area = filter_var($area, FILTER_VALIDATE_FLOAT);

            $population = (string)simplexml_load_string($population);
            $population = filter_var(trim($population), FILTER_VALIDATE_INT);

            $district = $districtOriginal = $orm->getRepository(District::class)->findOneBy([
                'cityId' => self::$cityId,
                'externalId' => $i
            ]);

            if(!$district) {
                $district = new District();
                $district->setExternalId($i);
                $district->setCityId(self::$cityId);
            }

            if($district->getName() != $districtName) $district->setName($districtName);
            if($district->getArea() != $area) $district->setArea($area);
            if($district->getPopulation() != $population) $district->setPopulation($population);

            if(!$districtOriginal) {
                $entityManager->persist($district);
            }
        }

        $entityManager->flush();

        $output->writeln("END SYNC");

        return Command::SUCCESS;
    }
}