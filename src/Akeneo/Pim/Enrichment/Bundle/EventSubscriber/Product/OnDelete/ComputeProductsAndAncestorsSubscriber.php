<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Removes products from the search engine. Also, reindexes the ancestor product models, as the deletion of products
 * may have changed the completeness 'all_complete' or 'all_incomplete' projections
 *
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class ComputeProductsAndAncestorsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        private Client $esClient
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() : array
    {
        return [
            StorageEvents::POST_REMOVE => ['deleteProduct'],
            StorageEvents::POST_REMOVE_ALL => ['deleteProducts'],
        ];
    }

    public function deleteProduct(RemoveEvent $event): void
    {
        $product = $event->getSubject();
        if (!$product instanceof ProductInterface) {
            return;
        }
        // TODO TIP-987 Remove this when decoupling PublishedProduct from Enrichment
        if (get_class($product) == 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct') {
            return;
        }
        if (!$event->hasArgument('unitary') || true !== $event->getArgument('unitary')) {
            return;
        }

        if ($this->elasticsearchShouldBeRefreshed([$product])) {
            $this->esClient->refreshIndex();
        }

        $this->productAndAncestorsIndexer->removeFromProductUuidsAndReindexAncestors(
            [$event->getSubjectId()],
            $this->getAncestorCodes([$product])
        );
    }

    public function deleteProducts(RemoveEvent $event): void
    {
        $products = $event->getSubject();
        if (!is_array($products) || !is_array($event->getSubjectId())) {
            return;
        }
        $products = array_filter($products, function ($product) {
            return $product instanceof ProductInterface
                // TODO TIP-987 Remove this when decoupling PublishedProduct from Enrichment
                && get_class($product) !== 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct';
        });

        if ($this->elasticsearchShouldBeRefreshed($products)) {
            $this->esClient->refreshIndex();
        }

        if (!empty($products)) {
            $this->productAndAncestorsIndexer->removeFromProductUuidsAndReindexAncestors(
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

    /**
     * PIM-10467: If a product is created then deleted very quickly, ES can not have the product yet because
     * it is refreshed every second. We take 10 seconds by security in case of overload.
     *
     * @param ProductInterface[] $products
     * @return bool
     */
    private function elasticsearchShouldBeRefreshed(array $products): bool
    {
        foreach ($products as $product) {
            if (null !== $product->getCreated() && 10 > time() - $product->getCreated()->getTimestamp()) {
                return true;
            }
        }

        return false;
    }
}
