<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Tool\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Index products in the search engine.
 *
 * This is not done directly in the product saver as it's only a technical
 * problem. The product saver only handles business stuff.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductsSubscriber implements EventSubscriberInterface
{
    /** @var IndexerInterface */
    private $productIndexer;

    /** @var BulkIndexerInterface */
    private $productBulkIndexer;

    /** @var RemoverInterface */
    private $productIndexRemover;

    /**
     * @param IndexerInterface     $productIndexer
     * @param BulkIndexerInterface $productBulkIndexer
     * @param RemoverInterface     $productIndexRemover
     */
    public function __construct(
        IndexerInterface $productIndexer,
        BulkIndexerInterface $productBulkIndexer,
        RemoverInterface $productIndexRemover
    ) {
        $this->productIndexer = $productIndexer;
        $this->productBulkIndexer = $productBulkIndexer;
        $this->productIndexRemover = $productIndexRemover;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() : array
    {
        return [
            StorageEvents::POST_SAVE     => ['indexProduct', 300],
            StorageEvents::POST_SAVE_ALL => ['bulkIndexProducts', 300],
            StorageEvents::PRE_REMOVE    => ['deleteProduct', 300],
        ];
    }

    /**
     * Index one single product.
     *
     * @param GenericEvent $event
     */
    public function indexProduct(GenericEvent $event) : void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        if (!$event->hasArgument('products_to_index') || !array_key_exists($product->getIdentifier(), $event->getArgument('products_to_index'))) {
            return;
        }

        $this->productIndexer->index($product);
    }

    /**
     * Index several products at a time.
     *
     * @param GenericEvent $event
     */
    public function bulkIndexProducts(GenericEvent $event) : void
    {
        $products = $event->getSubject();
        if (!is_array($products)) {
            return;
        }

        if (!current($products) instanceof ProductInterface) {
            return;
        }

        if (!$event->hasArgument('products_to_index')) {
            return;
        }
        $productIdentifiersToIndex = $event->getArgument('products_to_index');
        $this->productBulkIndexer->indexAll(array_filter($products, function (ProductInterface $product) use ($productIdentifiersToIndex) {
            return array_key_exists($product->getIdentifier(), $productIdentifiersToIndex);
        }));
    }

    /**
     * Delete one single product from ES index
     *
     * @param RemoveEvent $event
     */
    public function deleteProduct(RemoveEvent $event) : void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->productIndexRemover->remove($event->getSubjectId());
    }
}
