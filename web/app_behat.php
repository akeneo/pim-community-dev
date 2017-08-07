<?php

use Symfony\Component\HttpFoundation\Request;

ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';

$env = getenv('BEHAT_ENV');

if ($env == null) {
    $env = 'behat';
}

$kernel = new AppKernel($env, false);
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
