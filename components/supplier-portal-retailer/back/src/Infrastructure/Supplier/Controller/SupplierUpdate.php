<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\Supplier\Controller;

use Akeneo\SupplierPortal\Retailer\Application\Supplier\Exception\InvalidData;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\UpdateSupplier;
use Akeneo\SupplierPortal\Retailer\Application\Supplier\Write\UpdateSupplierHandler;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\SupplierContributorsBelongingToAnotherSupplier;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Write\Exception\SupplierDoesNotExist;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

final class SupplierUpdate
{
    public function __construct(
        private UpdateSupplierHandler $updateSupplierHandler,
        private SupplierContributorsBelongingToAnotherSupplier $supplierContributorsBelongToAnotherSupplier,
    ) {
    }

    public function __invoke(Request $request, string $identifier): JsonResponse
    {
        $requestContent = json_decode($request->getContent(), true);

        if (!isset($requestContent['label']) && !isset($requestContent['contributors'])) {
            return new JsonResponse(null, Response::HTTP_BAD_REQUEST);
        }

        $availableContributorEmails = $this->filterContributorEmailsBelongingToAnotherSupplier(
            $identifier,
            $requestContent['contributors'],
        );

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
        } catch (SupplierDoesNotExist) {
            return new JsonResponse(null, Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function filterContributorEmailsBelongingToAnotherSupplier(string $identifier, array $contributorEmails): array
    {
        $contributorsBelongingToAnotherSupplier = ($this->supplierContributorsBelongToAnotherSupplier)(
            $identifier,
            $contributorEmails,
        );

        return array_diff($contributorEmails, $contributorsBelongingToAnotherSupplier);
    }
}
