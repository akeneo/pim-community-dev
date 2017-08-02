<?php

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Bundle\CatalogBundle\Elasticsearch\Indexer\ProductIndexer;
use Pim\Component\Catalog\Model\ProductInterface;
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
    protected $productIndexer;

    /** @var BulkIndexerInterface */
    protected $productBulkIndexer;

    /** @var RemoverInterface */
    protected $productIndexRemover;

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
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE     => 'indexProduct',
            StorageEvents::POST_SAVE_ALL => 'bulkIndexProducts',
            StorageEvents::POST_REMOVE   => 'deleteProduct',
        ];
    }

    /**
     * Index one single product.
     *
     * @param GenericEvent $event
     */
    public function indexProduct(GenericEvent $event)
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $this->productIndexer->index($product);
    }

    /**
     * Index several products at a time.
     *
     * @param GenericEvent $event
     */
    public function bulkIndexProducts(GenericEvent $event)
    {
        $products = $event->getSubject();
        if (!is_array($products)) {
            return;
        }

        if (!current($products) instanceof ProductInterface) {
            return;
        }

        $this->productBulkIndexer->indexAll($products);
    }

    /**
     * Delete one single product from ES index
     *
     * @param RemoveEvent $event
     */
    public function deleteProduct(RemoveEvent $event)
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }

        $this->productIndexRemover->remove($event->getSubjectId());
    }
}
