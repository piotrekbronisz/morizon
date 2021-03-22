<?php

namespace App\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Image;


class ImageController extends AbstractController
{
    /**
     * @Route("/image/{imageName}", name="front_image_getImage")
     */
    public function getImageAction(Image $imageService, $imageName)
    {
        $image = $imageService->getImage($imageName);
        $response = new Response();
        $response->headers->set("Content-Type", $image['mimeType']);
        $response->setContent($image['src']);

        return $response;
    }

    /**
     * @Route("/image/{sidePx}/{imageName}", name="front_image_getImageWithSide")
     */
    public function getImageWithSideAction(Image $imageService, $imageName, $sidePx)
    {
        $image = $imageService->getImage($imageName, (int)$sidePx);
        $response = new Response();
        $response->headers->set("Content-Type", $image['mimeType']);
        $response->setContent($image['src']);

        return $response;
    }
}