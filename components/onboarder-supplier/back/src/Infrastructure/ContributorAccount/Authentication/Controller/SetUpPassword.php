<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\OnboarderSerenity\Retailer\Domain\Authentication\ContributorAccount\Read\GetContributorAccountIdByValidAccessToken;
use Symfony\Component\HttpFoundation\JsonResponse;

final class SetUpPassword
{
    public function __construct(private GetContributorAccountIdByValidAccessToken $isAccessTokenValid)
    {
    }

    public function __invoke(): JsonResponse
    {
        return new JsonResponse();
    }
}
