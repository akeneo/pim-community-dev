<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Read\Event\ProductFileDownloaded;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LogOnProductFileDownloaded implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductFileDownloaded::class => 'logOnProductFileDownloaded',
        ];
    }

    public function logOnProductFileDownloaded(ProductFileDownloaded $productFileDownloaded): void
    {
        $this->logger->info(
            'Product file downloaded.',
            [
                'data' => [
                    'supplier_file_identifier' => $productFileDownloaded->supplierFileIdentifier,
                    'metric_key' => 'product_file_downloaded',
                ],
            ],
        );
    }
}
