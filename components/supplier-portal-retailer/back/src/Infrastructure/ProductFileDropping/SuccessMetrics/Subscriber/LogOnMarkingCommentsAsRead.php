<?php

declare(strict_types=1);

namespace Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\SuccessMetrics\Subscriber;

use Akeneo\SupplierPortal\Retailer\Domain\ProductFileDropping\Write\Event\MarkingCommentsAsRead;
use Akeneo\SupplierPortal\Retailer\Infrastructure\ProductFileDropping\Query\Sql\DatabaseComputeCommentsReadDelay;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class LogOnMarkingCommentsAsRead implements EventSubscriberInterface
{
    public function __construct(
        private readonly DatabaseComputeCommentsReadDelay $databaseComputeCommentsReadDelay,
        private readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MarkingCommentsAsRead::class => 'handleMarkingCommentsAsRead',
        ];
    }

    public function handleMarkingCommentsAsRead(MarkingCommentsAsRead $event): void
    {
        $commentsReadDelay = ($this->databaseComputeCommentsReadDelay)($event->date, $event->productFileIdentifier);

        foreach ($commentsReadDelay as $commentReadDelay) {
            $this->logger->info(
                'A product file comment has been read',
                [
                    'data' => [
                        'metric_key' => 'product_file_comment_read',
                        'comment_read_delay_in_seconds' => $commentReadDelay,
                    ],
                ],
            );
        }
    }
}
