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
 * Index product models in the search engine.
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductModelsSubscriber implements EventSubscriberInterface
{
    /** @var IndexerInterface */
    protected $productModelIndexer;

    /** @var BulkIndexerInterface */
    protected $productModelBulkIndexer;

    /** @var RemoverInterface */
    protected $productModelIndexRemover;

    /**
     * @param IndexerInterface     $productModelIndexer
     * @param BulkIndexerInterface $productModelBulkIndexer
     * @param RemoverInterface     $productModelIndexRemover
     */
    public function __construct(
        IndexerInterface $productModelIndexer,
        BulkIndexerInterface $productModelBulkIndexer,
        RemoverInterface $productModelIndexRemover
    ) {
        $this->productModelIndexer = $productModelIndexer;
        $this->productModelBulkIndexer = $productModelBulkIndexer;
        $this->productModelIndexRemover = $productModelIndexRemover;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() : array
    {
        return [
            StorageEvents::POST_SAVE     => 'indexProductModel',
            StorageEvents::POST_SAVE_ALL => 'bulkIndexProductModels',
            StorageEvents::POST_REMOVE   => 'deleteProductModel',
        ];
    }

    /**
     * Index one product model.
     *
     * @param GenericEvent $event
     */
    public function indexProductModel(GenericEvent $event) : void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductModelInterface) {
            return;
        }

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $this->productModelIndexer->index($product);
    }

    /**
     * Index several product models.
     *
     * @param GenericEvent $event
     */
    public function bulkIndexProductModels(GenericEvent $event) : void
    {
        $products = $event->getSubject();
        if (!is_array($products)) {
            return;
        }

        if (!current($products) instanceof ProductModelInterface) {
            return;
        }

        $this->productModelBulkIndexer->indexAll($products);
    }

    /**
     * Delete one product model from ES index
     *
     * @param RemoveEvent $event
     */
    public function deleteProductModel(RemoveEvent $event) : void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductModelInterface) {
            return;
        }

        $this->productModelIndexRemover->remove($event->getSubjectId());
    }
}
