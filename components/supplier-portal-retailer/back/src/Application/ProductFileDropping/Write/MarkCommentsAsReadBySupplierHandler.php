<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\MarkCommentsAsReadBySupplier as MarkCommentsAsReadBySupplierQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;

class MarkCommentsAsReadBySupplierHandler
{
    public function __construct(
        private MarkCommentsAsReadBySupplierQuery $markCommentsAsReadBySupplier,
        private ProductFileRepository $productFileRepository,
    ) {
    }

    public function __invoke(MarkCommentsAsReadBySupplier $markCommentsAsReadBySupplier): void
    {
        $productFile = $this->productFileRepository->find(
            Identifier::fromString($markCommentsAsReadBySupplier->productFileIdentifier),
        );

        if (null === $productFile) {
            throw new ProductFileDoesNotExist();
        }
        if (!$productFile->hasComments()) {
            return;
        }

        ($this->markCommentsAsReadBySupplier)($markCommentsAsReadBySupplier->productFileIdentifier, $markCommentsAsReadBySupplier->lastReadAt);
    }
}
