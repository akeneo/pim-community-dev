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

$exifAdapter = new \DamEnterprise\Component\Metadata\Adapter\ExifAdapter();
$iptcAdapter = new \DamEnterprise\Component\Metadata\Adapter\IptcAdapter();

$registry = new \DamEnterprise\Component\Metadata\Adapter\AdapterRegistry();
$registry->add($exifAdapter);
$registry->add($iptcAdapter);

$factory = new \DamEnterprise\Component\Metadata\MetadataFactory($registry);

foreach ($images as $image) {
    $file = new SplFileInfo(realpath(__DIR__ . '/../../images/' . $image));
    $md = $factory->create($file);
    for ($i = 0; $i < 10000; $i++) {
        $mds = $md->all($file);
//        var_dump($mds);
    }
}
