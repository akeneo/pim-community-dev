<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnSave;

use Akeneo\Pim\Enrichment\Bundle\Storage\Sql\ProductModel\GetAncestorAndDescendantProductModelCodes;
use Akeneo\Pim\Enrichment\Component\Product\EntityWithFamily\Event\ParentHasBeenRemovedFromVariantProduct;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Storage\Indexer\ProductModelIndexerInterface;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * This subscriber reindexes the former ancestor product models of a variant product converted to a simple product
 *
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ReindexFormerAncestorsSubscriber implements EventSubscriberInterface
{
    /** @var GetAncestorAndDescendantProductModelCodes */
    private $getAncestorProductModelCodes;

    /** @var ProductModelIndexerInterface */
    private $productModelIndexer;

    /** @var array */
    private $formerParentCodes = [];

    public function __construct(
        GetAncestorAndDescendantProductModelCodes $getAncestorProductModelCodes,
        ProductModelIndexerInterface $productModelIndexer
    ) {
        $this->getAncestorProductModelCodes = $getAncestorProductModelCodes;
        $this->productModelIndexer = $productModelIndexer;
    }

    public static function getSubscribedEvents()
    {
        return [
            ParentHasBeenRemovedFromVariantProduct::class => 'store',
            StorageEvents::POST_SAVE => 'reIndex',
            StorageEvents::POST_SAVE_ALL => 'reIndexAll',
        ];
    }

    public function store(ParentHasBeenRemovedFromVariantProduct $event): void
    {
        $this->formerParentCodes[$event->getProduct()->getId()] = $event->getFormerParentProductModelCode();
    }

    public function reIndex(GenericEvent $event): void
    {
        $product = $event->getSubject();
        $unitary = $event->getArguments()['unitary'] ?? false;
        if (false === $unitary || empty($this->formerParentCodes) || !$product instanceof ProductInterface) {
            return;
        }

        $formerParentCode = $this->formerParentCodes[$product->getId()] ?? null;
        if (null !== $formerParentCode) {
            unset($this->formerParentCodes[$product->getId()]);
            $this->reindexFromProductModelCodes([$formerParentCode]);
        }
    }

    public function reIndexAll(GenericEvent $event): void
    {
        $products = $event->getSubject();
        if (empty($this->formerParentCodes) || !reset($products) instanceof ProductInterface) {
            return;
        }

        $formerParentCodes = [];
        foreach ($products as $product) {
            $formerParentCode = $this->formerParentCodes[$product->getId()] ?? null;
            if (null !== $formerParentCode) {
                unset($this->formerParentCodes[$product->getId()]);
                $formerParentCodes[] = $formerParentCode;
            }
        }

        $this->reindexFromProductModelCodes(array_values(array_unique($formerParentCodes)));
    }

    private function reindexFromProductModelCodes(array $productModelCodes): void
    {
        if ([] === $productModelCodes) {
            return;
        }
        $rootProductModelCodes = $this->getAncestorProductModelCodes->fromProductModelCodes($productModelCodes);

        $this->productModelIndexer->indexFromProductModelCodes(array_merge($productModelCodes, $rootProductModelCodes));
    }
}
