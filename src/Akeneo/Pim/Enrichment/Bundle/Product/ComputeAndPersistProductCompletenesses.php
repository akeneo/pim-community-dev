<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessWithMissingAttributeCodesCollection;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Akeneo\Pim\Enrichment\Product\API\Event\Completeness\ProductsCompletenessWereChanged;
use Akeneo\Pim\Enrichment\Product\Domain\Clock;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ComputeAndPersistProductCompletenesses
{
    private const CHUNK_SIZE = 1000;

    public function __construct(
        private CompletenessCalculator $completenessCalculator,
        private SaveProductCompletenesses $saveProductCompletenesses,
        private GetProductCompletenesses $getProductCompletenesses,
        private EventDispatcherInterface $eventDispatcher,
        private Clock $clock,
    ) {
    }

    /**
     * @param UuidInterface[] $productUuids
     */
    public function fromProductUuids(array $productUuids): void
    {
        foreach (array_chunk($productUuids, self::CHUNK_SIZE) as $uuidsChunk) {
            $previousCompletenessCollections = $this->getProductCompletenesses->fromProductUuids($uuidsChunk);
            $completenessCollections = $this->completenessCalculator->fromProductUuids($uuidsChunk);
            $this->saveProductCompletenesses->saveAll($completenessCollections);

            $productsCompletenessCollectionsWereChanged = $this->buildEvent($completenessCollections, $previousCompletenessCollections);
            if (null !== $productsCompletenessCollectionsWereChanged) {
                $this->eventDispatcher->dispatch($productsCompletenessCollectionsWereChanged);
            }
        }
    }

    /**
     * @param array<string, ProductCompletenessWithMissingAttributeCodesCollection> $newProductsCompletenessCollections
     * @param array<string, ProductCompletenessCollection> $previousProductsCompletenessCollections
     */
    private function buildEvent(
        array $newProductsCompletenessCollections,
        array $previousProductsCompletenessCollections
    ): ?ProductsCompletenessWereChanged {
        $changedAt = $this->clock->now();
        $changedProductsCompletenesses = [];
        foreach ($newProductsCompletenessCollections as $uuid => $newProductCompletenessCollection) {
            $previousProductCompletenessCollection = $previousProductsCompletenessCollections[$uuid] ?? null;

            $changedProductsCompletenesses = [
                ...$changedProductsCompletenesses,
                ...$newProductCompletenessCollection->buildProductCompletenessWasChangedEvents($changedAt, $previousProductCompletenessCollection)
            ];
        }

        return [] !== $changedProductsCompletenesses
            ? new ProductsCompletenessWereChanged($changedProductsCompletenesses)
            : null;
    }
}
