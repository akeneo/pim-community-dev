<?php

use LiveCodeCoverage\RemoteCodeCoverage;
use Symfony\Component\HttpFoundation\Request;

ini_set('display_errors', '1');
error_reporting(E_ALL);

require __DIR__.'/../vendor/autoload.php';


$shutDownCodeCoverage = RemoteCodeCoverage::bootstrap(
    //(bool)getenv('CODE_COVERAGE_ENABLED'),
    true,
    sys_get_temp_dir(),
    __DIR__ . '/../app/phpunit.xml.dist'
);

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


$shutDownCodeCoverage();
