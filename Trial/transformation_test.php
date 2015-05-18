<?php

$loader = require_once __DIR__ . '/../../vendor/autoload.php';

$images = [
    'my-image.jpg'
];

$resizeResolver = new \Akeneo\Component\FileTransformer\Options\Image\ResizeOptionsResolver();
$thumbnailResolver = new \Akeneo\Component\FileTransformer\Options\Image\ThumbnailOptionsResolver();
$resolutionResolver = new \Akeneo\Component\FileTransformer\Options\Image\ResolutionOptionsResolver();
$colorSpaceResolver = new \Akeneo\Component\FileTransformer\Options\Image\ColorSpaceOptionsResolver();
$scaleResolver = new \Akeneo\Component\FileTransformer\Options\Image\ScaleOptionsResolver();

$resizeTransformation = new \Akeneo\Component\FileTransformer\Transformation\Image\Resize($resizeResolver);
$thumbnailTransformation = new \Akeneo\Component\FileTransformer\Transformation\Image\Thumbnail($thumbnailResolver);
$resolutionTransformation = new \Akeneo\Component\FileTransformer\Transformation\Image\Resolution($resolutionResolver);
$colorSpaceTransformation = new \Akeneo\Component\FileTransformer\Transformation\Image\ColorSpace($colorSpaceResolver);
$scaleTransformation = new \Akeneo\Component\FileTransformer\Transformation\Image\Scale($scaleResolver);

$registry = new \Akeneo\Component\FileTransformer\Transformation\TransformationRegistry();
$registry->add($resizeTransformation);
$registry->add($thumbnailTransformation);
$registry->add($resolutionTransformation);
$registry->add($colorSpaceTransformation);
$registry->add($scaleTransformation);

$transformer = new \Akeneo\Component\FileTransformer\FileTransformer($registry);

$transformationsPipeline = [
    [
        'outputFile' => 'test.jpg',
        'pipeline'   => [
            'wrong-transformation' => [],
            'resize' => ['wrong options'],
            'resolution' => ['resolution' => 5, 'resolution-unit' => 'ppi'],
            'thumbnail' => ['width' => 100, 'height' => 100],
//            'resize' => ['width' => 400, 'height' => 50],
            'colorspace' => ['colorspace' => 'gray'],
            'scale' => ['ratio' => 0.5],
//            'scale' => ['width' => 1000],
//            'scale' => ['height' => 1000],
        ]
    ],
    [
        'outputFile' => 'test2.jpg',
        'pipeline'   => [
            'wrong-transformation' => [],
            'resize' => ['wrong options'],
            'resolution' => ['resolution' => 5, 'resolution-unit' => 'ppi'],
            'thumbnail' => ['width' => 100, 'height' => 100],
//            'resize' => ['width' => 400, 'height' => 50],
            'colorspace' => ['colorspace' => 'gray'],
            'scale' => ['ratio' => 0.5],
//            'scale' => ['width' => 1000],
//            'scale' => ['height' => 1000],
        ]
    ]
];

foreach ($images as $image) {
    $file = new SplFileInfo(realpath(__DIR__ . '/../../images/transformations/' . $image));
    try {
        $transformer->transform($file, $transformationsPipeline);
        echo "Image $image transformed.\n";
    } catch (\Akeneo\Component\FileTransformer\Exception\NotApplicableTransformationException $e) {
        echo "Impossible to transform the image $image.\n";
    }
}
