<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Product;

use Akeneo\Pim\Enrichment\Component\Product\Completeness\CompletenessCalculator;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompleteness;
use Akeneo\Pim\Enrichment\Component\Product\Completeness\Model\ProductCompletenessCollection;
use Akeneo\Pim\Enrichment\Component\Product\Event\ProductsCompletenessWereChangedEvent;
use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductCompletenesses;
use Akeneo\Pim\Enrichment\Component\Product\Query\SaveProductCompletenesses;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
        private EventDispatcher $eventDispatcher,
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

            $changedProductsCompleteness = $this->computeChangedProductsCompleteness($completenessCollections, $previousCompletenessCollections);
            if (!empty($changedProductsCompleteness)) {
                $this->eventDispatcher->dispatch(new ProductsCompletenessWereChangedEvent($changedProductsCompleteness));
            }
        }
    }

    /**
     * @param array<string, ProductCompletenessCollection> $productsCompleteness
     * @param array<string, ProductCompletenessCollection> $previousProductsCompleteness
     *
     * @return array<string, ProductCompletenessCollection>
     */
    private function computeChangedProductsCompleteness(array $productsCompleteness, array $previousProductsCompleteness): array
    {
        $changedProductsCompleteness = [];
        foreach ($productsCompleteness as $uuid => $productCompleteness) {
            if (!\array_key_exists($uuid, $previousProductsCompleteness)) {
                $changedProductsCompleteness[$uuid] = $productCompleteness;
                continue;
            }

            $previousProductCompleteness = $previousProductsCompleteness[$uuid];
            $changedProductCompleteness = [];

            /** @var ProductCompleteness $completeness */
            foreach ($productCompleteness as $completeness) {
                $previousCompleteness = $previousProductCompleteness->getCompletenessForChannelAndLocale($completeness->channelCode(), $completeness->localeCode());
                if ($previousCompleteness && $previousCompleteness->ratio() !== $completeness->ratio()) {
                    $changedProductCompleteness[] = $completeness;
                }
            }

            if (!empty($changedProductCompleteness)) {
                $changedProductsCompleteness[$uuid] = new ProductCompletenessCollection($productCompleteness->productUuid(), $changedProductCompleteness);
            }
        }

        return $changedProductsCompleteness;
    }
}
