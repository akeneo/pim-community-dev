<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\InMemory;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\MarkCommentsAsReadByRetailer;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Repository\InMemory\InMemoryRepository;

final class InMemoryMarkCommentsAsReadByRetailer implements MarkCommentsAsReadByRetailer
{
    public function __construct(private InMemoryRepository $productFileRepository)
    {
    }

    public function __invoke(string $productFileIdentifier, \DateTimeImmutable $readAt): void
    {
        $this->productFileRepository->updateProductFileLastReadAtDateForRetailer(Identifier::fromString($productFileIdentifier), $readAt);
    }
}
