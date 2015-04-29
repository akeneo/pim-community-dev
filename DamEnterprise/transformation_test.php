<?php

$loader = require_once __DIR__ . '/../../vendor/autoload.php';

$images = [
    //'akene.jpg',
    //'aurora.jpg',
    'boat.jpg',
    /*
    'col.jpg',
    'EPSN0043.jpg',
    'IMG_0002.jpg',
    'IMG_0011.tiff',
    'IMG_0012.jpg',
    'IMG_7376.JPG.jpg',
    'IMGP9262.jpg',
    'nin.jpg'*/
];

$resizeTransformation = new \DamEnterprise\Component\Transformer\Transformation\Image\Resize();
$resolutionTransformation = new \DamEnterprise\Component\Transformer\Transformation\Image\Resolution();

$registry = new \DamEnterprise\Component\Transformer\Transformation\TransformationRegistry();
$registry->add($resizeTransformation);
$registry->add($resolutionTransformation);

$transformer = new \DamEnterprise\Component\Transformer\Transformer($registry);
$rawTransformations = [
    //'wrong-transformation' => [],
    //'resize' => ['wrong options'],
    //'resize' => ['width' => 100, 'height' => 40],
    'resolution' => ['resolution' => 5, 'resolution-unit' => 'ppi']
];

foreach ($images as $image) {
    $file = new SplFileInfo(realpath(__DIR__ . '/../../images/transformations/' . $image));
    $transformer->transform($file, $rawTransformations);
}
