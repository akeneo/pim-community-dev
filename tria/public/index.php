<?php

use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;

require dirname(__DIR__).'/config/bootstrap.php';

if ($_SERVER['APP_DEBUG']) {
    umask(0000);

    Debug::enable();
}

if ($trustedProxies = $_SERVER['TRUSTED_PROXY_IPS'] ?? $_ENV['TRUSTED_PROXY_IPS'] ?? false) {
    Request::setTrustedProxies(explode(',', $trustedProxies),Request::HEADER_X_FORWARDED_ALL);
    // Tell onelogin-saml to trust the proxy, too: @see https://github.com/hslavich/OneloginSamlBundle/issues/114
    \OneLogin\Saml2\Utils::setProxyVars(true);
}

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
