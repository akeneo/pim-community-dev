<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplier\MarkCommentsAsReadBySupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplier\MarkCommentsAsReadBySupplierHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;

final class MarkCommentsAsRead
{
    public function __construct(private MarkCommentsAsReadBySupplierHandler $markCommentsAsReadBySupplierHandler)
    {
    }

    public function __invoke(MarkCommentsAsReadCommand $markCommentsAsReadCommand): void
    {
        try {
            ($this->markCommentsAsReadBySupplierHandler)(
                new MarkCommentsAsReadBySupplier(
                    $markCommentsAsReadCommand->productFileIdentifier,
                    $markCommentsAsReadCommand->lastReadAt,
                )
            );
        } catch (ProductFileDoesNotExist) {
            throw new Exception\ProductFileDoesNotExist();
        }
    }
}
