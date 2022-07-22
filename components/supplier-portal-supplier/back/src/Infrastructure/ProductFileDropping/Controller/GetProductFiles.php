<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\ProductFileDropping\Controller;

use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccount;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class GetProductFiles
{
    public function __invoke(#[CurrentUser] ?ContributorAccount $user): JsonResponse
    {
        if (!$user instanceof ContributorAccount) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
