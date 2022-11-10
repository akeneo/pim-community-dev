<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\RequestNewInvitation\RequestNewInvitation as RequestNewInvitationCommand;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\RequestNewInvitation\RequestNewInvitationHandler;
use Akeneo\SupplierPortal\Supplier\Domain\Authentication\ContributorAccount\Write\Exception\ContributorAccountDoesNotExist;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class RequestNewInvitation
{
    public function __construct(private RequestNewInvitationHandler $requestNewInvitationHandler)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$request->request->has('email')) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->requestNewInvitationHandler)(new RequestNewInvitationCommand(
                $request->get('email'),
                new \DateTimeImmutable(),
            ));
        } catch (ContributorAccountDoesNotExist) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse();
    }
}
