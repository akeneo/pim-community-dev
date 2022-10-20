<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead;

use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplier;
use Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Write\MarkCommentsAsReadBySupplierHandler;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotExist;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Exception\ProductFileDoesNotHaveComments;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead\Exception\ProductFileDoesNotExist as ProductFileDoesNotExistServiceAPI;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\ServiceAPI\MarkCommentsAsRead\Exception\ProductFileDoesNotHaveComments as ProductFileDoesNotHaveCommentsServiceAPI;

final class MarkCommentsAsRead
{
    public function __construct(private MarkCommentsAsReadBySupplierHandler $markCommentsAsReadBySupplierHandler)
    {
    }

    public function __invoke(MarkCommentsAsReadCommand $markCommentsAsReadCommand)
    {
        try {
            ($this->markCommentsAsReadBySupplierHandler)(
                new MarkCommentsAsReadBySupplier(
                    $markCommentsAsReadCommand->productFileIdentifier,
                    $markCommentsAsReadCommand->lastReadAt,
                )
            );
        } catch (ProductFileDoesNotHaveComments) {
            throw new ProductFileDoesNotHaveCommentsServiceAPI();
        } catch (ProductFileDoesNotExist) {
            throw new ProductFileDoesNotExistServiceAPI();
        }
    }
}
