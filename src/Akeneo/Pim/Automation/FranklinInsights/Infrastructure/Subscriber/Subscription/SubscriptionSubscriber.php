<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Subscriber\Subscription;

use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductsSubscribed;
use Akeneo\Pim\Automation\FranklinInsights\Domain\Subscription\Events\ProductSubscribed;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class SubscriptionSubscriber implements EventSubscriberInterface
{
    /** @var IndexerInterface */
    private $productIndexer;

    /** @var BulkIndexerInterface */
    private $bulkProductIndexer;

    public function __construct(IndexerInterface $productIndexer, BulkIndexerInterface $bulkProductIndexer)
    {
        $this->productIndexer = $productIndexer;
        $this->bulkProductIndexer = $bulkProductIndexer;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            ProductSubscribed::EVENT_NAME => 'reindexProduct',
            ProductsSubscribed::EVENT_NAME => 'bulkReindexProducts',
        ];
    }

    /**
     * On product subscribed it re-indexes product to change the franklin insights status in ES.
     *
     * @param ProductSubscribed $event
     */
    public function reindexProduct(ProductSubscribed $event): void
    {
        $this->productIndexer->index($event->getSubscribedProduct());
    }

    /**
     * On products subscribed it re-indexes products to change the franklin insights status in ES.
     *
     * @param ProductsSubscribed $event
     */
    public function bulkReindexProducts(ProductsSubscribed $event): void
    {
        $this->bulkProductIndexer->indexAll($event->getSubscribedProducts());
    }
}
