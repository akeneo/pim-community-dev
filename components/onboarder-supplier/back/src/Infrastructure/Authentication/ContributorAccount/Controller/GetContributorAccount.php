<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Read\GetContributorAccountByAccessToken;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class GetContributorAccount
{
    public function __construct(
        private GetContributorAccountByAccessToken $getContributorAccountByAccessToken,
    ) {
    }

    public function __invoke(string $accessToken): JsonResponse
    {
        $contributorAccount = ($this->getContributorAccountByAccessToken)(trim($accessToken));

        if (null === $contributorAccount->accessToken) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        if (!$contributorAccount->isAccessTokenValid(new \DateTimeImmutable())) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse($contributorAccount->toArray());
    }
}
