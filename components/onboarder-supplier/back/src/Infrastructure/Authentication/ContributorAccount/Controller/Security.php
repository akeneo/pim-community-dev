<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\OnboarderSerenity\Supplier\Domain\Authentication\ContributorAccount\Write\Model\ContributorAccount;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

final class Security
{
    public function login(#[CurrentUser] ?ContributorAccount $user): JsonResponse
    {
        if (!$user instanceof ContributorAccount) {
            return new JsonResponse(null, Response::HTTP_UNAUTHORIZED);
        }

        return new JsonResponse([
            'email' => $user->email(),
        ], Response::HTTP_OK);
    }
}
