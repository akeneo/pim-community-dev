<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Application\ProductFileDropping\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileDeleted;
use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\ProductFileRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class DeleteCommentsOnProductFileDeleted implements EventSubscriberInterface
{
    public function __construct(private ProductFileRepository $productFileRepository)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductFileDeleted::class => 'deleteComments',
        ];
    }

    public function deleteComments(ProductFileDeleted $productFileDeleted): void
    {
        $this->productFileRepository->deleteProductFileRetailerComments($productFileDeleted->productFileIdentifier);
        $this->productFileRepository->deleteProductFileSupplierComments($productFileDeleted->productFileIdentifier);
    }
}
