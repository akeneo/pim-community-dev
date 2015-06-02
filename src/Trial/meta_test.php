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

$exifAdapter = new \Akeneo\Component\FileMetadata\Adapter\Exif();
$iptcAdapter = new \Akeneo\Component\FileMetadata\Adapter\Iptc();

$registry = new \Akeneo\Component\FileMetadata\Adapter\AdapterRegistry();
$registry->add($exifAdapter);
$registry->add($iptcAdapter);

$factory = new \Akeneo\Component\FileMetadata\FileMetadataReaderFactory($registry);

foreach ($images as $image) {
    $file = new SplFileInfo(realpath(__DIR__ . '/../../images/' . $image));
    $md = $factory->create($file);
    for ($i = 0; $i < 1; $i++) {
        $mds = $md->all($file);
        echo "\n\n$image\n";
        var_dump($mds);
    }
}

$dumper = new \Akeneo\Component\FileMetadata\Adapter\AdapterDumper($registry);
$dumped = $dumper->dump('image/jpeg');
var_dump($dumped);
