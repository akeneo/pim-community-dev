<?php

use Composer\Autoload\ClassLoader;
use Doctrine\Common\Annotations\AnnotationRegistry;

/**
 * @var $loader ClassLoader
 */
$loader = require __DIR__.'/../vendor/autoload.php';

// add possibility to extends doctrine unit test and use mocks
$loader->add( 'Doctrine\\Tests', __DIR__.'/../vendor/doctrine/orm/tests' );

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;
