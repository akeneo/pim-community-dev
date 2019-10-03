<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Removes products from the search engine.
 *
 * Also Reindexes the ancestor product models, as the deletion of products may have changed the
 * completeness 'all_complete' or 'all_incomplete' projections
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DeleteProductsFromIndexSubscriber implements EventSubscriberInterface
{
    /** @var ProductAndAncestorsIndexer */
    private $productAndAncestorsIndexer;

    public function __construct(ProductAndAncestorsIndexer $productAndAncestorsIndexer)
    {
        $this->productAndAncestorsIndexer = $productAndAncestorsIndexer;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() : array
    {
        return [
            StorageEvents::POST_REMOVE   => ['deleteProduct'],
            StorageEvents::POST_REMOVE_ALL => ['deleteProducts'],
        ];
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
        if (!$event->hasArgument('unitary') || true !== $event->getArgument('unitary')) {
            return;
        }

        $this->productAndAncestorsIndexer->removeFromProductIdsAndReindexAncestors(
            [$event->getSubjectId()],
            $this->getAncestorCodes([$product])
        );
    }

    public function deleteProducts(RemoveEvent $event): void
    {
        $products = $event->getSubject();
        if (!is_array($products) || !is_array ($event->getSubjectId())) {
            return;
        }
        $products = array_filter($products, function ($product) {
            return $product instanceof ProductInterface
                // TODO TIP-987 Remove this when decoupling PublishedProduct from Enrichment
                && get_class($product) !== 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct';
        });

        if (!empty($products)) {
            $this->productAndAncestorsIndexer->removeFromProductIdsAndReindexAncestors(
                $event->getSubjectId(),
                $this->getAncestorCodes($products)
            );
        }
    }

    private function getAncestorCodes(array $products): array
    {
        $ancestorCodes = [];
        foreach ($products as $product) {
            $entityWithFamilyVariant = $product;
            while (null !== $entityWithFamilyVariant->getParent()) {
                $entityWithFamilyVariant = $entityWithFamilyVariant->getParent();
                $ancestorCodes[] = $entityWithFamilyVariant->getCode();
            }
        }

        return array_unique($ancestorCodes, SORT_STRING);
    }
}
