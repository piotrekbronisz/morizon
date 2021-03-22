<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Filter;
use App\Entity\District;
use App\Entity\City;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\FileBag;

class DistrictController extends AbstractController
{
    const _ROOT_PATH_ = __DIR__."/../../public/img/";

    /**
     * @Route("/district/{districtId}", name="district_options", methods={"OPTIONS"})
     */
    public function options(): Response
    {
        $response = new Response();
        $response->headers->set("Access-Control-Allow-Methods", "GET, POST");

        return $response;
    }

    /**
     * @Route("/district/{cityId}", name="district_index", methods={"GET"})
     */
    public function index($cityId, Request $request, Filter $serviceFilter): Response
    {
        $orm = $this->getDoctrine();
        $filters = $serviceFilter->getFilters($request->query->all(), ['name', 'areaFrom', 'areaTo', 'populationFrom', 'populationTo']);
        $filters['cityId'] = (int)$cityId;
        $sort = $serviceFilter->getSort($request->query->all(), ['nameSort', 'areaSort', 'populationSort']);
        $perPage = ($request->get('per_page')) ?: 20;
        $pageNo = ($request->get('page_no')) ?: 1;

        //try {
            $districts = $orm->getRepository(District::class)->getCollection($sort, $filters, $perPage, $pageNo);
        //} catch (\Exception $e) {
        //    $districts = false;
        //}

        return $this->render('district/index.html.twig', [
            'districts' => $districts
        ]);
    }

    /**
     * @Route("/district/{districtId}", name="district_update", methods={"POST"})
     */
    public function update($districtId, Request $request): Response
    {
        Sleep(1);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode(200);

        if($_FILES['image']['name']) {

            $imageNameExplode = explode(".", $_FILES['image']['name']);
            $imageNameType = strtolower($imageNameExplode[(count($imageNameExplode)-1)]);

            if(!in_array($imageNameType, ["jpg", "jpeg", "gif", "png", "svg"]) || !in_array(strtolower($_FILES['image']['type']), ["image/jpg", "image/jpeg", "image/gif", "image/png", "image/svg+xml"])) {
                $response->setStatusCode(500);
                return $response;
            }

            $rootPath = self::_ROOT_PATH_;
            $imagePath = "";
            $i = 1;
            while(true) {
                $filename = preg_replace("[^A-z-_0-9]", "", explode(".", $_FILES['image']['name'])[0]);
                $imagePathToSave = $filename."_".$i.".".$imageNameType;
                $imagePath = $rootPath.$imagePathToSave;
                if(!file_exists($imagePath)) {
                    break;
                }
                $i++;
            }

            $result = (bool)file_put_contents($imagePath, file_get_contents($_FILES['image']['tmp_name']));

            $orm = $this->getDoctrine();
            $em = $orm->getManager();
            $district = $orm->getRepository(District::class)->findOneById($districtId);
            $district->setImagePath($imagePathToSave);
            $em->flush();
            $response->setContent(json_encode(["image" => $imagePathToSave]));
        }


        return $response;
    }

    /**
     * @Route("/district/{cityId}/refresh", name="district_refresh", methods={"GET"})
     */
    public function refresh($cityId, KernelInterface $kernel): Response
    {
        $city = $this->getDoctrine()->getRepository(City::class)->findOneById($cityId);

        if($city) {
            $application = new Application($kernel);
            $application->setAutoExit(false);

            $input = new ArrayInput(['command' => 'sync:'.$city->getAlias()]);

            $output = new BufferedOutput();
            $application->run($input, $output);
        }

        return $this->redirectToRoute('district_index', ['cityId' => $cityId]);
    }
}
