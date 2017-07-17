<?php
//
//use Symfony\Component\ClassLoader\ApcClassLoader;
//use Symfony\Component\HttpFoundation\Request;
//
//$loader = require_once __DIR__.'/../app/bootstrap.php.cache';
//// Use APC for autoloading to improve performance.
//// Use the HOST variable if available to define prefix
///*
//$prefix = 'pim-behat';
//
//if (isset($_SERVER['HTTP_HOST'])) {
//    $prefix .= '-'.$_SERVER['HTTP_HOST'];
//}
//$loader = new ApcClassLoader($prefix, $loader);
//$loader->register(true);
//*/
//
//// if env defined outside (by vhost for example)
//// use it
//
//
//require_once __DIR__.'/../app/AppKernel.php';
//
//$kernel = new AppKernel($env, false);
//$kernel->loadClassCache();
//$request = Request::createFromGlobals();
//$response = $kernel->handle($request);
//$response->send();
//$kernel->terminate($request, $response);

use Symfony\Component\HttpFoundation\Request;

ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__.'/../app/autoload.php';
include_once __DIR__.'/../var/bootstrap.php.cache';

$env = getenv('BEHAT_ENV');

if ($env == null) {
    $env = 'behat';
}

$kernel = new AppKernel($env, false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);
// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
