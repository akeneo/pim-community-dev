<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadByRetailer;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\MarkCommentsAsReadByRetailer as MarkCommentsAsReadByRetailerQuery;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ValueObject\Identifier;

final class MarkCommentsAsReadByRetailerHandler
{
    public function __construct(
        private MarkCommentsAsReadByRetailerQuery $markCommentsAsReadByRetailerQuery,
        private ProductFileRepository $productFileRepository,
    ) {
    }

    public function __invoke(MarkCommentsAsReadByRetailer $markCommentsAsReadByRetailer): void
    {
        $productFile = $this->productFileRepository->find(
            Identifier::fromString($markCommentsAsReadByRetailer->productFileIdentifier),
        );

        if (null === $productFile) {
            throw new ProductFileDoesNotExist();
        }

        if (!$productFile->hasComments()) {
            return;
        }

        ($this->markCommentsAsReadByRetailerQuery)(
            $markCommentsAsReadByRetailer->productFileIdentifier,
            $markCommentsAsReadByRetailer->lastReadAt
        );
    }
}
