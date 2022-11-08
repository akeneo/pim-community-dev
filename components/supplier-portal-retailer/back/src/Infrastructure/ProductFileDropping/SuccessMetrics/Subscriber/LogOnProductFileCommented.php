<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\SuccessMetrics\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\ProductFileCommentedBySupplier;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LogOnProductFileCommented implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public static function getSubscribedEvents()
    {
        return [
            ProductFileCommentedBySupplier::class => 'logOnProductFileCommentedBySupplier',
        ];
    }

    public function logOnProductFileCommentedBySupplier(ProductFileCommentedBySupplier $productFileCommentedBySupplier): void
    {
        $this->logger->info(
            sprintf('Contributor "%s" commented a product file.', $productFileCommentedBySupplier->authorEmail()),
            [
                'data' => [
                    'identifier' => $productFileCommentedBySupplier->productFileIdentifier(),
                    'content' => $productFileCommentedBySupplier->commentContent(),
                    'author_email' => $productFileCommentedBySupplier->authorEmail(),
                    'metric_key' => 'contributor_product_file_commented',
                ],
            ],
        );
    }
}
