<?php

use Doctrine\Common\Annotations\AnnotationRegistry;
use Composer\Autoload\ClassLoader;

/**
 * @var ClassLoader $loader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

$loader->add( 'Doctrine\\Tests', __DIR__.'/../vendor/doctrine/orm/tests' );

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

return $loader;
