<?php
declare(strict_types=1);

namespace Pim\Bundle\CatalogBundle\EventSubscriber;

use Akeneo\Component\StorageUtils\Indexer\IndexerInterface;
use Akeneo\Component\StorageUtils\StorageEvents;
use Pim\Component\Catalog\Model\ProductModelInterface;
use Pim\Component\Catalog\Model\VariantProductInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscriber for product
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductSubscriber implements EventSubscriberInterface
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
        $variantProduct = $event->getSubject();
        if (!$variantProduct instanceof VariantProductInterface) {
            return;
        }

        if (null === $productModel = $variantProduct->getParent()) {
            return;
        }

        $this->indexProductModel($variantProduct->getParent());
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
