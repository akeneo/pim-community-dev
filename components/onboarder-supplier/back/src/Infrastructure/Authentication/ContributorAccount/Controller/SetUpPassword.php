<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\UpdatePasswordHandler;
use Symfony\Component\HttpFoundation\JsonResponse;

final class SetUpPassword
{
    public function __construct(
        private UpdatePasswordHandler $updatePasswordHandler,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        return new JsonResponse();
    }
}
