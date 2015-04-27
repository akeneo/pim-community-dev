<?php

$loader = require_once __DIR__ . '/../../vendor/autoload.php';

//$image = new SplFileInfo(realpath(__DIR__ . '/../../images/nin.jpg'));
//$image = new SplFileInfo(realpath(__DIR__ . '/../../images/boat.jpg'));
$image = new SplFileInfo(realpath(__DIR__ . '/../../images/col.jpg'));

$exifAdapter = new \DamEnterprise\Component\Metadata\Adapter\ExifAdapter();
$iptcAdapter = new \DamEnterprise\Component\Metadata\Adapter\IptcAdapter();

$registry = new \DamEnterprise\Component\Metadata\Adapter\AdapterRegistry();
$registry->add($exifAdapter);
$registry->add($iptcAdapter);

$factory = new \DamEnterprise\Component\Metadata\MetadataFactory($registry);

var_dump($image);

$md  = $factory->create($image);
$mds = $md->all($image);

var_dump($mds);
