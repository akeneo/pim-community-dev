<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Authentication\ContributorAccount\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

final class SetUpPassword
{
    public function __invoke(): JsonResponse
    {
        return new JsonResponse();
    }
}
