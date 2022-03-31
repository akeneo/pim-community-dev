<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Application\Supplier\Exceptions\InvalidDataException;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplierHandler;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SupplierUpdate
{
    public function __construct(private UpdateSupplierHandler $updateSupplierHandler)
    {
    }

    public function __invoke(Request $request, string $identifier): JsonResponse
    {
        $requestContent = json_decode($request->getContent(), true);

        if (!isset($requestContent['label']) && !isset($requestContent['contributorEmails'])){
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        try {
            ($this->updateSupplierHandler)(
                new UpdateSupplier($identifier, $requestContent['label'], $requestContent['contributorEmails'])
            );
        } catch (InvalidDataException $e) {
            return new JsonResponse(null, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}
