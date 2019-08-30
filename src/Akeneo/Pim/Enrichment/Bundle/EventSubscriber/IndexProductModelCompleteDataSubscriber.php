<?php
declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Component\StorageUtils\Indexer\ProductIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
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
    /** @var ProductIndexerInterface */
    private $productModelIndexer;

    /**
     * @param ProductIndexerInterface $productModelIndexer
     */
    public function __construct(ProductIndexerInterface $productModelIndexer)
    {
        $this->productModelIndexer = $productModelIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            StorageEvents::POST_SAVE => ['computeNumberOfCompleteVariantProduct', 300],
            StorageEvents::POST_SAVE_ALL => ['computeNumberOfCompleteVariantProducts', 300]
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
        $isUnitary = $event->getArguments()['unitary'] ?? true;

        if (!$isUnitary) {
            return;
        }

        $this->computeNumberOfCompleteVariantProducts(new GenericEvent([$event->getSubject()], $event->getArguments()));
    }

    public function computeNumberOfCompleteVariantProducts(GenericEvent $event): void
    {
        $products = $event->getSubject();

        $products = array_filter($products, function ($product): bool {
            return $product instanceof ProductInterface && $product->isVariant();
        });

        foreach ($products as $product) {
            $this->indexProductModel($product->getParent());
        }
    }

    /**
     * @param ProductModelInterface $productModel
     */
    private function indexProductModel(ProductModelInterface $productModel): void
    {
        if (null !== $parent = $productModel->getParent()) {
            $this->indexProductModel($parent);
        }

        $this->productModelIndexer->indexFromProductIdentifier($productModel->getCode());
    }
}
