<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Infrastructure\InternalApi\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class AppActivationController
{
    public function __invoke(Request $request, string $identifier)
    {
        $pimSource = 'http://172.17.0.1:8080';
        $nativeAppUrl = 'http://172.17.0.1:8081/activate';
        $yellExtensionRedirectUrl = "${nativeAppUrl}?pim=${pimSource}";

        return RedirectResponse::create($yellExtensionRedirectUrl);
    }
}
