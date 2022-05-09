<?php

declare(strict_types=1);

namespace Akeneo\OnboarderSerenity\Application\Supplier;

use Akeneo\OnboarderSerenity\Domain\Supplier\Write\Repository;
use Akeneo\OnboarderSerenity\Domain\Supplier\Write\ValueObject\Identifier;
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

        $this->logger->debug(
            sprintf('Supplier "%s" deleted.', $deleteSupplier->identifier),
        );
    }
}
