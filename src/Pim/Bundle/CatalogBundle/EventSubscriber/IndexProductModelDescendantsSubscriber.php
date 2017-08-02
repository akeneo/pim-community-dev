<?php

declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Component\StorageUtils\Indexer\BulkIndexerInterface;
use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\Remover\RemoverInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Index product models descendance in the search engine.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductModelDescendantsSubscriber implements EventSubscriberInterface
{
    /** @var IndexerInterface */
    protected $productModelDescendantsIndexer;

    /** @var BulkIndexerInterface */
    protected $productModelDescendantsBulkIndexer;

    /** @var RemoverInterface */
    protected $productModelDescendantsRemover;

    /**
     * @param IndexerInterface     $productModelIndexer
     * @param BulkIndexerInterface $ProductModelbulkIndexer
     * @param RemoverInterface     $productModelRemover
     */
    public function __construct(
        IndexerInterface $productModelIndexer,
        BulkIndexerInterface $ProductModelbulkIndexer,
        RemoverInterface $productModelRemover
    ) {
        $this->productModelDescendantsIndexer = $productModelIndexer;
        $this->productModelDescendantsBulkIndexer = $ProductModelbulkIndexer;
        $this->productModelDescendantsRemover = $productModelRemover;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() : array
    {
        return [
            StorageEvents::POST_SAVE     => 'indexProductModelDescendants',
            StorageEvents::POST_SAVE_ALL => 'bulkIndexProductModelsDescendants',
            StorageEvents::POST_REMOVE   => 'deleteProductModelDescendants',
        ];
    }

    /**
     * Index one product model descendants.
     *
     * @param GenericEvent $event
     */
    public function indexProductModelDescendants(GenericEvent $event) : void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductModelInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $this->productModelDescendantsIndexer->index($product);
    }

    /**
     * Index several product models descendants.
     *
     * @param GenericEvent $event
     */
    public function bulkIndexProductModelsDescendants(GenericEvent $event) : void
    {
        $products = $event->getSubject();
        if (!is_array($products)) {
            return;
        }

        if (!current($products) instanceof ProductModelInterface) {
            return;
        }

        $this->productModelDescendantsBulkIndexer->indexAll($products);
    }

    /**
     * Remove one product model descendants from the index.
     *
     * @param RemoveEvent $event
     */
    public function deleteProductModelDescendants(RemoveEvent $event) : void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductModelInterface) {
            return;
        }

        $this->productModelDescendantsRemover->remove($product);
    }
}
