<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Read\GetContributorAccountByAccessToken;
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

        if (!$contributorAccount->isAccessTokenValid) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($contributorAccount->toArray());
    }
}
