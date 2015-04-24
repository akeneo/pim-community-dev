<?php

$loader = require_once __DIR__ . '/../../vendor/autoload.php';

//$image = new SplFileInfo(realpath(__DIR__ . '/../../images/nin.jpg'));
//$image = new SplFileInfo(realpath(__DIR__ . '/../../images/boat.jpg'));
$image = new SplFileInfo(realpath(__DIR__ . '/../../images/col.jpg'));

$exifAdapter = new \Akeneo\DAM\Component\Metadata\Adapter\ExifAdapter();
$iptcAdapter = new \Akeneo\DAM\Component\Metadata\Adapter\IptcAdapter();

$registry = new \Akeneo\DAM\Component\Metadata\Adapter\AdapterRegistry();
$registry->add($exifAdapter);
$registry->add($iptcAdapter);

$factory = new \Akeneo\DAM\Component\Metadata\MetadataFactory($registry);

var_dump($image);

$md  = $factory->create($image);
$mds = $md->all($image);

var_dump($mds);
