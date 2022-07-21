<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Security\ContributorAccount;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class CheckAuthentication
{
    public function __invoke(#[CurrentUser] ?ContributorAccount $user, Request $request): JsonResponse
    {
        if (
            !$user instanceof ContributorAccount ||
            !$request->request->has('email') ||
            $request->request->get('email') !== $user->getUserIdentifier()
        ) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }
}
