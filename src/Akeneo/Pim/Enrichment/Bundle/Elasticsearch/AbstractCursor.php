<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * Common logic shared by all our product and product model cursors.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCursor implements CursorInterface
{
    protected Client $esClient;
    protected ProductRepositoryInterface $productRepository;
    protected ProductModelRepositoryInterface $productModelRepository;
    protected array $esQuery;
    protected ?array $items = null;
    protected ?int $count = null;
    protected int $position = 0;

    /**
     * {@inheritdoc}
     */
    public function current(): mixed
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function key(): mixed
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return key($this->items) + $this->position;
    }

    /**
     * {@inheritdoc}
     */
    public function valid(): bool
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return !empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function count(): int
    {
        if (null === $this->count) {
            $esQuery = \array_replace($this->esQuery, ['track_total_hits' => true]);

            $response = $this->esClient->search($esQuery);
            $this->count = $response['hits']['total']['value'];
        }

        return $this->count;
    }

    /**
     * Get the next items (hydrated from doctrine repository).
     *
     * @param array $esQuery
     *
     * @return array
     */
    protected function getNextItems(array $esQuery): array
    {
        return $this->getNextItemsFromIdentifiers($this->getNextIdentifiers($esQuery));
    }

    protected function getNextItemsFromIdentifiers(IdentifierResults $identifierResults): array
    {
        if ($identifierResults->isEmpty()) {
            return [];
        }

        $hydratedProducts = $this->productRepository->getItemsFromUuids(
            $identifierResults->getProductUuids()
        );
        $hydratedProductModels = $this->productModelRepository->getItemsFromIdentifiers(
            $identifierResults->getProductModelIdentifiers()
        );
        $hydratedItems = array_merge($hydratedProducts, $hydratedProductModels);

        $orderedItems = [];

        foreach ($identifierResults->all() as $identifierResult) {
            foreach ($hydratedItems as $hydratedItem) {
                if ($hydratedItem instanceof ProductInterface &&
                    $identifierResult->getId() === \sprintf('product_%s', $hydratedItem->getUuid()->toString())
                ) {
                    $orderedItems[] = $hydratedItem;
                    break;
                } elseif ($hydratedItem instanceof ProductModelInterface &&
                    $identifierResult->isProductModelIdentifierEquals($hydratedItem->getCode())
                ) {
                    $orderedItems[] = $hydratedItem;
                    break;
                }
            }
        }

        return $orderedItems;
    }

    /**
     * Returns the next identifier results.
     * The idea is keep the sort of the identifiers and to be able to know if it's a product or a product model.
     *
     * @return IdentifierResults
     */
    abstract protected function getNextIdentifiers(array $esQuery): IdentifierResults;
}
