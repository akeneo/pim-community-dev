<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$loader = require __DIR__.'/../vendor/autoload.php';

// intl
if (!function_exists('intl_get_error_code')) {
    require_once __DIR__.'/../vendor/symfony/symfony/src/Symfony/Component/Locale/Resources/stubs/functions.php';
}

// add possibility to extends doctrine unit test and use mocks
$loader->add( 'Doctrine\\Tests', __DIR__.'/../vendor/doctrine/orm/tests' );

AnnotationRegistry::registerLoader(array($loader, 'loadClass'));

return $loader;