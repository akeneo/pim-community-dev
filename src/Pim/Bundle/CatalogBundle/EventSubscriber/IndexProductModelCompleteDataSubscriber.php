<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscriber for product
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IndexProductModelCompleteDataSubscriber implements EventSubscriberInterface
{
    /** @var IndexerInterface */
    private $productModelIndexer;

    /**
     * @param IndexerInterface $productModelIndexer
     */
    public function __construct(IndexerInterface $productModelIndexer)
    {
        $this->productModelIndexer = $productModelIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => 'computeNumberOfCompleteVariantProduct'
        ];
    }

    /**
     * After a variant product saving we need to update product and product model index, the field at_least_complete and
     * update at_least_complete are updated. Those two fields are used by the completeness filter to display complete or
     * incomplete product model.
     *
     * @param GenericEvent $event
     */
    public function computeNumberOfCompleteVariantProduct(GenericEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface || !$product->isVariant()) {
            return;
        }

        if (null === $productModel = $product->getParent()) {
            return;
        }

        $this->indexProductModel($productModel);
    }

    /**
     * @param ProductModelInterface $productModel
     */
    private function indexProductModel(ProductModelInterface $productModel): void
    {
        if (null !== $parent = $productModel->getParent()) {
            $this->indexProductModel($parent);
        }

        $this->productModelIndexer->index($productModel);
    }
}
