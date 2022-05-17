<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\EventSubscriber\Product\OnDelete;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductAndAncestorsIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Component\StorageUtils\Event\RemoveEvent;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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
    /**
     * @var UuidInterface[]
     */
    private array $productUuidsCache = [];

    public function __construct(
        private ProductAndAncestorsIndexer $productAndAncestorsIndexer,
        private Connection $connection
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents() : array
    {
        return [
            StorageEvents::PRE_REMOVE => ['setProductUuidCache'],
            StorageEvents::PRE_REMOVE_ALL => ['setProductUuidsCache'],
            StorageEvents::POST_REMOVE => ['deleteProduct'],
            StorageEvents::POST_REMOVE_ALL => ['deleteProducts'],
        ];
    }

    public function setProductUuidCache(RemoveEvent $event): void
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

        $this->setProductUuidsCacheInner([$event->getSubjectId()]);
    }

    public function setProductUuidsCache(RemoveEvent $event): void
    {
        $products = $event->getSubject();
        if (!is_array($products)) {
            return;
        }
        $products = array_filter($products, function ($product) {
            return $product instanceof ProductInterface
                // TODO TIP-987 Remove this when decoupling PublishedProduct from Enrichment
                && get_class($product) !== 'Akeneo\Pim\WorkOrganization\Workflow\Component\Model\PublishedProduct';
        });

        $this->setProductUuidsCacheInner(array_map(fn (ProductInterface $product): int => $product->getId(), $products));
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

        $this->productAndAncestorsIndexer->removeFromProductIdsAndReindexAncestors(
            [$event->getSubjectId()],
            $this->productUuidsCache,
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

        if (!empty($products)) {
            $this->productAndAncestorsIndexer->removeFromProductIdsAndReindexAncestors(
                $event->getSubjectId(),
                $this->productUuidsCache,
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

    private function setProductUuidsCacheInner(array $productIds): void
    {
        if (0 === count($productIds)) {
            $this->productUuidsCache = [];

            return;
        }

        $result = $this->connection->fetchFirstColumn(<<<SQL
            SELECT BIN_TO_UUID(uuid) AS uuid
            FROM pim_catalog_product
            WHERE id IN (:product_ids)
            AND uuid IS NOT NULL
        SQL, [
            'product_ids' => $productIds
        ], [
            'product_ids' => Connection::PARAM_INT_ARRAY
        ]);

        $this->productUuidsCache = array_map(
            fn (string $uuid): UuidInterface => Uuid::fromString($uuid),
            $result
        );
    }
}
