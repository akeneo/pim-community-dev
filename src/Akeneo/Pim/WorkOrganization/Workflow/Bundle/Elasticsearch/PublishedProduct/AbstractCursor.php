<?php

declare(strict_types=1);

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\Elasticsearch\PublishedProduct;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Pim\WorkOrganization\Workflow\Component\Repository\PublishedProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
abstract class AbstractCursor implements CursorInterface
{
    protected Client $esClient;
    protected PublishedProductRepositoryInterface $publishedProductRepository;
    protected ?array $items = null;
    protected ?int $count = null;
    protected int $position = 0;

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

        return key($this->items) + $this->position;
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
     * @return array<string>
     */
    protected function getNextItems(array $esQuery): array
    {
        return $this->getNextItemsFromIdentifiers($this->getNextIdentifiers($esQuery));
    }

    protected function getNextItemsFromIdentifiers(array $identifiers): array
    {
        if (empty($identifiers)) {
            return [];
        }

        $hydratedItems = $this->publishedProductRepository->findBy(['identifier' => $identifiers]);
        $orderedItems = [];
        foreach ($identifiers as $identifier) {
            foreach ($hydratedItems as $hydratedItem) {
                if ((string) $identifier === $hydratedItem->getIdentifier()) {
                    $orderedItems[] = $hydratedItem;
                    break;
                }
            }
        }

        return $orderedItems;
    }

    abstract protected function getNextIdentifiers(array $esQuery): array;
}
