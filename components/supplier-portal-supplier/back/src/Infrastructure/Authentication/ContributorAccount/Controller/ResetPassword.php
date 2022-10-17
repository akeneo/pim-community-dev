<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Supplier\Infrastructure\Authentication\ContributorAccount\Controller;

use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\ResetPassword as ResetPasswordCommand;
use Akeneo\SupplierPortal\Supplier\Application\Authentication\ContributorAccount\Write\ResetPasswordHandler;
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
