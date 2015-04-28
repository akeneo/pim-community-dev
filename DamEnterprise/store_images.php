<?php

use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesser;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

require_once '/home/jjanvier/workspaces/phpstorm/akeneo/pim_master/ped/vendor/autoload.php';
require_once '/home/jjanvier/workspaces/phpstorm/akeneo/pim_master/ped/app/AppKernel.php';

$kernel = new AppKernel('dev', true);
$kernel->loadClassCache();
$kernel->boot();

$directory = realpath(__DIR__ . '/../../images/');
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

$pathGenerator = new \Trial\Storage\StoragePathGenerator();
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

    $file = new \DamEnterprise\Component\Asset\Model\File();
    $file->setFilename($storage['file_name']);
    $file->setGuid($storage['guid']);
    $file->setMimeType($mimeType);
    $file->setOriginalFilename($imageName);
    $file->setPath($storage['path']);

    var_dump($file);

    $em->persist($file);
    $filesystem->copy($imageName, 'stored/' . $file->getPath());
}

$em->flush();
