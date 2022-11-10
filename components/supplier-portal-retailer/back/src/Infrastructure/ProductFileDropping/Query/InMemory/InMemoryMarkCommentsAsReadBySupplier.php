<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\MarkCommentsAsReadBySupplier;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Model\ProductFile\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository;

final class InMemoryMarkCommentsAsReadBySupplier implements MarkCommentsAsReadBySupplier
{
    public function __construct(private InMemoryRepository $productFileRepository)
    {
    }

    public function __invoke(string $productFileIdentifier, \DateTimeImmutable $readAt): void
    {
        $this->productFileRepository->updateProductFileLastReadAtDateForSupplier(Identifier::fromString($productFileIdentifier), $readAt);
    }
}
