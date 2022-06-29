<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\Exception\ContributorAccountDoesNotExist;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\RequestNewInvitation as RequestNewInvitationCommand;
use Akeneo\OnboarderSerenity\Supplier\Application\Authentication\ContributorAccount\RequestNewInvitationHandler;
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
            ($this->requestNewInvitationHandler)(new RequestNewInvitationCommand($request->get('email')));
        } catch (ContributorAccountDoesNotExist) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse();
    }
}
