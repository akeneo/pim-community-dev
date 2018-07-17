<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Common logic shared by all our product and product model cursors.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractCursor implements CursorInterface
{
    /** @var Client */
    protected $esClient;

    /** @var CursorableRepositoryInterface */
    protected $productRepository;

    /** @var CursorableRepositoryInterface */
    protected $productModelRepository;

    /** @var array */
    protected $items;

    /** @var int */
    protected $count;

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return current($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function key()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return key($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function valid()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        return !empty($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        if (null === $this->items) {
            $this->rewind();
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
        $identifierResults = $this->getNextIdentifiers($esQuery);
        if ($identifierResults->isEmpty()) {
            return [];
        }

        $hydratedProducts = $this->productRepository->getItemsFromIdentifiers(
            $identifierResults->getProductIdentifiers()
        );
        $hydratedProductModels = $this->productModelRepository->getItemsFromIdentifiers(
            $identifierResults->getProductModelIdentifiers()
        );
        $hydratedItems = array_merge($hydratedProducts, $hydratedProductModels);

        $orderedItems = [];

        foreach ($identifierResults->all() as $identifierResult) {
            foreach ($hydratedItems as $hydratedItem) {
                if ($hydratedItem instanceof ProductInterface &&
                    $identifierResult->isProductIdentifierEquals($hydratedItem->getIdentifier())
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
