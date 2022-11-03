<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch;

use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductDraft;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Model\ProductModelDraft;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Tool\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Common logic shared by all our product and product model draft cursors.
 *
 * @author Olivier Soulet <olivier.soulet@akeneo.com>
 */
abstract class AbstractCursor implements CursorInterface
{
    /** @var Client */
    protected $esClient;

    /** @var CursorableRepositoryInterface */
    protected $productDraftRepository;

    /** @var CursorableRepositoryInterface */
    protected $productModelDraftRepository;

    /** @var array */
    protected $items;

    /** @var int */
    protected $count;

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

        return key($this->items);
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

        $hydratedProducts = $this->productDraftRepository->getItemsFromIdentifiers(
            $identifierResults->getProductIdentifiers()
        );
        $hydratedProductModels = $this->productModelDraftRepository->getItemsFromIdentifiers(
            $identifierResults->getProductModelIdentifiers()
        );
        $hydratedItems = array_merge($hydratedProducts, $hydratedProductModels);

        $orderedItems = [];

        foreach ($identifierResults->all() as $identifierResult) {
            foreach ($hydratedItems as $hydratedItem) {
                if ($hydratedItem instanceof ProductDraft &&
                    $identifierResult->isProductDraftIdentifierEquals($hydratedItem->getIdentifier())
                    || $hydratedItem instanceof ProductModelDraft &&
                    $identifierResult->isProductModelDraftIdentifierEquals($hydratedItem->getIdentifier())
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
