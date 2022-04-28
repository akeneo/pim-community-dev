<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Application\Supplier\Exception\InvalidData;
use Akeneo\OnboarderSerenity\Application\Supplier\Exception\SupplierDoesNotExist;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplier;
use Akeneo\OnboarderSerenity\Application\Supplier\UpdateSupplierHandler;
use Akeneo\OnboarderSerenity\Domain\Supplier\Read\SupplierContributorsBelongingToAnotherSupplier;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SupplierUpdate
{
    public function __construct(private UpdateSupplierHandler $updateSupplierHandler, private SupplierContributorsBelongingToAnotherSupplier $supplierContributorsBelongToAnotherSupplier)
    {
    }

    public function __invoke(Request $request, string $identifier): JsonResponse
    {
        $requestContent = json_decode($request->getContent(), true);

        if (!isset($requestContent['label']) && !isset($requestContent['contributors'])) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $availableContributorEmails = $this->filterEmailsBelongingToAnotherSupplier($identifier, $requestContent['contributors']);

        try {
            ($this->updateSupplierHandler)(
                new UpdateSupplier($identifier, $requestContent['label'], $availableContributorEmails)
            );
        } catch (InvalidData $e) {
            $errors = [];
            foreach ($e->violations() as $violation) {
                $errors[] = [
                    'propertyPath' => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                    'invalidValue' => $violation->getInvalidValue(),
                ];
            }

            return new JsonResponse($errors, Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (SupplierDoesNotExist $e) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function filterEmailsBelongingToAnotherSupplier(string $identifier, array $contributors): array
    {
        $contributorsBelongingToAnotherSupplier = ($this->supplierContributorsBelongToAnotherSupplier)(
            $identifier,
            $contributors,
        );

        return array_diff($contributors, $contributorsBelongingToAnotherSupplier);
    }
}
