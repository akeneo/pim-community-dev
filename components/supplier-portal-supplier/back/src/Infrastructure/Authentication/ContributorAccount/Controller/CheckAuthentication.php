<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class CheckAuthentication
{
    public function __invoke(Request $request): JsonResponse
    {
        return new JsonResponse();
    }
}
