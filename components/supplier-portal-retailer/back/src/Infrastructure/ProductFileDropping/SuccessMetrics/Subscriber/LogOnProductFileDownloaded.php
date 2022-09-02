<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\SuccessMetrics\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Event\ProductFileDownloaded;
use Akeneo\SupplierPortal\Retailer\Domain\Supplier\Read\GetSupplierCodeFromSupplierFileIdentifier;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LogOnProductFileDownloaded implements EventSubscriberInterface
{
    public function __construct(
        private GetSupplierCodeFromSupplierFileIdentifier $getSupplierCodeFromSupplierFileIdentifier,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductFileDownloaded::class => 'logOnProductFileDownloaded',
        ];
    }

    public function logOnProductFileDownloaded(ProductFileDownloaded $productFileDownloaded): void
    {
        $supplierCode = ($this->getSupplierCodeFromSupplierFileIdentifier)($productFileDownloaded->supplierFileIdentifier);

        $this->logger->info(
            'Product file downloaded.',
            [
                'data' => [
                    'metric_key' => 'product_file_downloaded',
                    'supplier_code' => $supplierCode,
                    'supplier_file_identifier' => $productFileDownloaded->supplierFileIdentifier,
                    'user_id' => $productFileDownloaded->userId,
                ],
            ],
        );
    }
}
