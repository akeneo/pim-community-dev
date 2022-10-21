<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\Exception\InvalidPassword;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\UpdatePassword;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\UpdatePasswordHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Exception\ContributorAccountDoesNotExist;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Exception\UserHasNotConsent;
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
                    (bool) $request->get('consent', false),
                )
            );
        } catch (InvalidPassword | UserHasNotConsent) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        } catch (ContributorAccountDoesNotExist) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_ACCEPTED);
    }
}
