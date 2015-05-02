<?php

$loader = require_once __DIR__ . '/../../vendor/autoload.php';

$images = [
    'akene.jpg',
    'aurora.jpg',
    'boat.jpg',
    'col.jpg',
    'EPSN0043.jpg',
    'IMG_0002.jpg',
    'IMG_0011.tiff',
    'IMG_0012.jpg',
    'IMG_7376.JPG.jpg',
    'IMGP9262.jpg',
    'nin.jpg'
];

$resizeTransformation = new \DamEnterprise\Component\Transformer\Transformation\Image\Resize();
$thumbnailTransformation = new \DamEnterprise\Component\Transformer\Transformation\Image\Thumbnail();
$resolutionTransformation = new \DamEnterprise\Component\Transformer\Transformation\Image\Resolution();
$colorSpaceTransformation = new \DamEnterprise\Component\Transformer\Transformation\Image\ColorSpace();
$scaleTransformation = new \DamEnterprise\Component\Transformer\Transformation\Image\Scale();

$registry = new \DamEnterprise\Component\Transformer\Transformation\TransformationRegistry();
$registry->add($resizeTransformation);
$registry->add($thumbnailTransformation);
$registry->add($resolutionTransformation);
$registry->add($colorSpaceTransformation);
$registry->add($scaleTransformation);

$transformer = new \DamEnterprise\Component\Transformer\Transformer($registry);
$rawTransformations = [
    //'wrong-transformation' => [],
    //'resize' => ['wrong options'],

    //'resolution' => ['resolution' => 5, 'resolution-unit' => 'ppi']
    //'thumbnail' => ['width' => 100, 'height' => 100]
    //'resize' => ['width' => 400, 'height' => 50],
    'colorspace' => ['colorspace' => 'gray'],
    //'scale' => ['ratio' => 0.5],
    //'scale' => ['width' => 1000],
    //'scale' => ['height' => 1000],
];

foreach ($images as $image) {
    $file = new SplFileInfo(realpath(__DIR__ . '/../../images/transformations/' . $image));
    try {
        $transformer->transform($file, $rawTransformations);
        echo "Image $image transformed.\n";
    } catch (\DamEnterprise\Component\Transformer\Exception\NotApplicableTransformationException $e) {
        echo "Impossible to transform the image $image.\n";
    }
}
