<?php

use Symfony\Component\HttpFoundation\Request;

if (!isset($_SERVER['PHP_AUTH_USER'])) {
    $_SERVER['PHP_AUTH_USER'] = 'admin';
    $_SERVER['PHP_AUTH_PW'] = 'admin';
}

$loader = require_once __DIR__.'/../app/bootstrap.php.cache';

require_once __DIR__.'/../app/AppKernel.php';
//require_once __DIR__.'/../app/AppCache.php';

$kernel = new AppKernel('prod', false);
$kernel->loadClassCache();
//$kernel = new AppCache($kernel);

// When using the HttpCache, you need to call the method in your front controller instead of relying on the configuration parameter
//Request::enableHttpMethodParameterOverride();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
