<?php
$loader = require_once __DIR__.'/../vendor/autoload.php';

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader->add('Doctrine\\Tests', __DIR__.'/../vendor/doctrine/orm/tests');

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
