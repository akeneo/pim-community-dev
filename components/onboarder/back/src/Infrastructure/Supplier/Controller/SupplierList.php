<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Symfony\Component\HttpFoundation\Response;

final class SupplierList
{
    public function __invoke(): Response
    {
        return new Response('foo');
    }
}
