<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Infrastructure\Supplier\Controller;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Read\SupplierContributorsBelongingToAnotherSupplier;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class GetSupplierContributorsBelongingToAnotherSupplier
{
    public function __construct(
        private SupplierContributorsBelongingToAnotherSupplier $supplierContributorsBelongToAnotherSupplier,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(Request $request, string $supplierIdentifier): JsonResponse
    {
        $urlEncodedEmails = $request->query->get('emails');
        if (empty($urlEncodedEmails)) {
            return new JsonResponse([]);
        }

        try {
            $emails = \json_decode($urlEncodedEmails, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            $this->logger->warning(
                sprintf(
                    'The following error occurred while decoding contributor emails: "%s"',
                    $e->getMessage(),
                ),
            );
            return new JsonResponse([]);
        }

        return new JsonResponse(
            ($this->supplierContributorsBelongToAnotherSupplier)(
                $supplierIdentifier,
                $emails,
            ),
        );
    }
}
