<?php

use LiveCodeCoverage\RemoteCodeCoverage;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

$shutDownCodeCoverage = RemoteCodeCoverage::bootstrap(
//(bool)getenv('CODE_COVERAGE_ENABLED'),
    true,
    sys_get_temp_dir(),
    __DIR__ . '/../phpunit.xml.dist'
);

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXY_IPS'] ?? $_ENV['TRUSTED_PROXY_IPS'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies),Request::HEADER_X_FORWARDED_ALL);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);

$shutDownCodeCoverage();
