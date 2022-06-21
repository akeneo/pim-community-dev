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
        $decodedRequest = \json_decode($request->getContent(), true);
        if (!array_key_exists('contributorAccountIdentifier', $decodedRequest)
            || !array_key_exists('plainTextPassword', $decodedRequest)) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->updatePasswordHandler)(
                new UpdatePassword(
                    $decodedRequest['contributorAccountIdentifier'],
                    $decodedRequest['plainTextPassword'],
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
