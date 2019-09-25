<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Index product models in the search engine.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductModelsSubscriber implements EventSubscriberInterface
{
    /** @var ProductModelIndexerInterface */
    private $productModelIndexer;

    public function __construct(ProductModelIndexerInterface $productModelIndexer)
    {
        $this->productModelIndexer = $productModelIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() : array
    {
        return [
            StorageEvents::POST_SAVE       => 'indexProductModel',
            StorageEvents::POST_SAVE_ALL   => 'bulkIndexProductModels',
            StorageEvents::POST_REMOVE     => 'deleteProductModel',
            StorageEvents::POST_REMOVE_ALL => 'bulkDeleteProductModels',
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

        $this->productModelIndexer->indexFromProductModelCode($product->getCode());
    }

    /**
     * Index several product models.
     *
     * @param GenericEvent $event
     */
    public function bulkIndexProductModels(GenericEvent $event) : void
    {
        $productModels = $event->getSubject();
        if (!is_array($productModels)) {
            return;
        }

        if (!current($productModels) instanceof ProductModelInterface) {
            return;
        }

        $this->productModelIndexer->indexFromProductModelCodes(
            array_map(function (ProductModelInterface $productModel) {
                return $productModel->getCode();
            }, $productModels)
        );
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

        if (!$event->hasArgument('unitary') || false === $event->getArgument('unitary')) {
            return;
        }

        $this->productModelIndexer->removeFromProductModelId($event->getSubjectId());
    }

    /**
     * Delete several product models from ES index
     *
     * @param RemoveEvent $event
     */
    public function bulkDeleteProductModels(RemoveEvent $event): void
    {
        $productModels = $event->getSubject();
        if (!is_array($productModels) || !current($productModels) instanceof ProductModelInterface) {
            return;
        }

        $this->productModelIndexer->removeFromProductModelIds(
            array_map(function (ProductModelInterface $product) {
                return $product->getId();
            }, $productModels)
        );
    }
}
