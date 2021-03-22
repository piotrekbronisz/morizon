<?php

namespace App\Service;

use Gumlet\ImageResize;

class Image
{
    const _BASE_IMAGES_PATH_ = __DIR__ . "/../../public/img/";

    public function getImage($imageName, $sidePx = null)
    {
        $mimeTypeMap = [
            "jpg" => "image/jpg",
            "jpeg" => "image/jpeg",
            "gif" => "image/gif",
            "png" => "image/png",
            "svg" => "image/svg+xml"
        ];

        if ($sidePx != null) {
            $pathToImageInCache = self::_BASE_IMAGES_PATH_ . "cache/" . $sidePx . "_" . $imageName;
        } else {
            $pathToImageInCache = self::_BASE_IMAGES_PATH_ . "cache/" . $imageName;
        }

        $contentType = $mimeTypeMap[(explode(".", $imageName)[1])];

        if (!file_exists($pathToImageInCache)) {
            $pathToImage = self::_BASE_IMAGES_PATH_ . $imageName;

            $image = new ImageResize($pathToImage);
            if ($sidePx) $image->resizeToLongSide($sidePx);
            $image->save($pathToImageInCache);
        }

        return [
            "mimeType" => $contentType,
            "src" => file_get_contents($pathToImageInCache)
        ];
    }
}