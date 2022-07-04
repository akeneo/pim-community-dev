<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\ResetPassword as ResetPasswordCommand;
use Akeneo\SupplierPortal\Application\Authentication\ContributorAccount\ResetPasswordHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class ResetPassword
{
    public function __construct(private ResetPasswordHandler $resetPasswordHandler)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        if (!$request->request->has('email')) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        ($this->resetPasswordHandler)(new ResetPasswordCommand($request->get('email')));

        return new JsonResponse();
    }
}
