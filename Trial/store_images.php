<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

require_once '/home/willy/project/akeneo/pim-enterprise-dev/vendor/autoload.php';
require_once '/home/willy/project/akeneo/pim-enterprise-dev/app/AppKernel.php';

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

$pathGenerator = new \PimEnterprise\Component\ProductAsset\FileStorage\PathGenerator();
$filesystem = new \League\Flysystem\Filesystem(new \League\Flysystem\Adapter\Local($directory));
$em = $kernel->getContainer()->get('doctrine.orm.default_entity_manager');

if (is_dir($directory . '/stored')) {
    exec(sprintf('rm %s/stored/ -rf', $directory));
}

foreach ($images as $imageName) {
    $imagePath = $directory . '/' .$imageName;
    $imageFile = new \SplFileInfo($imagePath);
    $mimeType = MimeTypeGuesser::getInstance()->guess($imagePath);
    $storage = $pathGenerator->generate($imageFile);

    $file = new \PimEnterprise\Component\ProductAsset\Model\File();
    $file->setFilename($storage['file_name']);
    $file->setGuid($storage['guid']);
    $file->setMimeType($mimeType);
    $file->setOriginalFilename($imageName);
    $file->setPath($storage['path_name']);
    $file->setSize(filesize($imagePath));

    var_dump($file);

    $em->persist($file);
    $filesystem->copy($imageName, 'stored/' . $file->getPath());
}

$em->flush();
