<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Retailer\Application\Supplier;

use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\Repository;
use Akeneo\OnboarderSerenity\Retailer\Domain\Supplier\Write\ValueObject\Identifier;
use Psr\Log\LoggerInterface;

final class DeleteSupplierHandler
{
    public function __construct(
        private Repository $supplierRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(DeleteSupplier $deleteSupplier): void
    {
        $this->supplierRepository->delete(
            Identifier::fromString($deleteSupplier->identifier),
        );

        $this->logger->info(
            sprintf('Supplier "%s" deleted.', $deleteSupplier->identifier),
        );
    }
}
