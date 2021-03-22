<?php
namespace App\Command\Sync;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Container\ContainerInterface;
use App\Entity\District;

class GdanskCommand extends Command
{
    private $client;
    private $container;
    private static $url = "https://www.gdansk.pl/subpages/dzielnice/html/4-dzielnice_mapa_alert.php";
    protected static $districtsMaxId = 35;
    protected static $cityId = 1;
    protected static $defaultName = 'sync:gdansk';

    public function __construct(HttpClientInterface $client, ContainerInterface $container)
    {
        parent::__construct();
        $this->client = $client;
        $this->container = $container;
    }

    protected function configure()
    {
        $this->setDescription("Getting data about Gdansk regions");
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

            $xml = simplexml_load_string($response->getContent());
            $districtName = (string)$xml->div[0];
            $area = (string)$xml->div[1];
            $population = (string)$xml->div[2];

            $area = trim(str_replace(["Powierzchnia:", "km", ","],["", "", "."], $area));
            $area = filter_var($area, FILTER_VALIDATE_FLOAT);
            $area = $area*100;

            $population = trim(str_replace(["Liczba ludności:", "osób"],"", $population));
            $population = filter_var($population, FILTER_VALIDATE_INT);

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