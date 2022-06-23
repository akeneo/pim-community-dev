<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\Exception\ContributorAccountDoesNotExist;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\Exception\InvalidPassword;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\UpdatePassword;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\UpdatePasswordHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SetUpPassword
{
    public function __construct(
        private UpdatePasswordHandler $updatePasswordHandler,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$request->request->has('contributorAccountIdentifier') || !$request->request->has('plainTextPassword')) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->updatePasswordHandler)(
                new UpdatePassword(
                    $request->get('contributorAccountIdentifier'),
                    $request->get('plainTextPassword'),
                )
            );
        } catch (InvalidPassword $e) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        } catch (ContributorAccountDoesNotExist $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
