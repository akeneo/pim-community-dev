<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Authentication\ContributorAccount\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;

final class SetUpPassword
{
    public function __invoke()
    {
        return new JsonResponse();
    }
}
