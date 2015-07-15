<?php

//require_once '/home/willy/project/akeneo/pim-enterprise-dev/vendor/autoload.php';
//require_once '/home/willy/project/akeneo/pim-enterprise-dev/app/AppKernel.php';

require_once '/home/jjanvier/workspaces/phpstorm/akeneo/pim_master/ped/vendor/autoload.php';
require_once '/home/jjanvier/workspaces/phpstorm/akeneo/pim_master/ped/app/AppKernel.php';

use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\HttpKernel\Kernel;

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$kernel->boot();

$directory = realpath(__DIR__ . '/../../dataset');

$images = [
    'hammer-en.jpg',
    'hammer-fr.jpg',
    'paint.jpg',
    'round-sofa-en.jpg',
    'round-sofa-fr.jpg',
    'square-sofa-en.jpg',
    'square-sofa-fr.jpg',
    'wrench-en.jpg',
    'wrench-fr.jpg'
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
$pathGenerator = new \Akeneo\Component\FileStorage\PathGenerator();
$filesystem = new \League\Flysystem\Filesystem(new \League\Flysystem\Adapter\Local($directory));
$em = $kernel->getContainer()->get('doctrine.orm.default_entity_manager');

function getVariationPipeline($image)
{
    $exploded  = explode('.', $image);
    $imageName = $exploded[0];
    $ext       = '.' . $exploded[1];

    return [
        getThumbnailVariation($imageName, $ext),
        getEcommerceVariation($imageName, $ext),
        getMobileVariation($imageName, $ext),
        getPrintVariation($imageName, $ext)
    ];
}

function getThumbnailVariation($imageName, $ext)
{
    return [
        'outputFile' => $imageName . '-thumbnail' . $ext,
        'pipeline'   => [
            'thumbnail' => ['width' => 150, 'height' => 150],
            'colorspace' => ['colorspace' => 'gray']
        ]
    ];
}

function getEcommerceVariation($imageName, $ext)
{
    return [
        'outputFile' => $imageName . '-ecommerce' . $ext,
        'pipeline'   => [
            'scale' => ['width' => 500],
        ]
    ];
}

function getMobileVariation($imageName, $ext)
{
    return [
        'outputFile' => $imageName . '-mobile' . $ext,
        'pipeline'   => [
            'scale' => ['width' => 250],
        ]
    ];
}

function getPrintVariation($imageName, $ext)
{
    return [
        'outputFile' => $imageName . '-print' . $ext,
        'pipeline'   => [
            'colorspace' => ['colorspace' => 'gray'],
        ]
    ];
}

foreach ($images as $image) {
    $fileInfo = new SplFileInfo(realpath(__DIR__ . '/../../dataset/' . $image));

    try {
        $pipeline = getVariationPipeline($image);

        foreach ($pipeline as $transformation) {
            $imageName = $transformation['outputFile'];
            $imagePath = $directory . '/' .$imageName;

            $transformer->transform($fileInfo, $transformation['pipeline'], $imageName);

            $imageFile = new \SplFileInfo($imagePath);
            $mimeType = MimeTypeGuesser::getInstance()->guess($imagePath);
            $storage = $pathGenerator->generate($imageFile);

            $file = new \Akeneo\Component\FileStorage\Model\File();
            $file->setFilename($storage['file_name']);
            $file->setGuid($storage['guid']);
            $file->setMimeType($mimeType);
            $file->setOriginalFilename($imageName);
            $file->setPath($storage['path']);
            $file->setSize(filesize($imagePath));

            $em->persist($file);
            $filesystem->copy($imageName, 'stored/' . $file->getKey());
        }

        echo "Image $image transformed.\n";
    } catch (\Akeneo\Component\FileTransformer\Exception\NotApplicableTransformationException $e) {
        echo "Impossible to transform the image $image.\n";
        var_dump($e);
    }
}

//$em->flush();

