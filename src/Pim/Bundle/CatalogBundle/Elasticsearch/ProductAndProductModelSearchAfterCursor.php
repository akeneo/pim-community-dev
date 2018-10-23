<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Bounded cursor to iterate over items where a start and a limit are defined.
 * Internally, this is implemented with the search after pagination.
 * {@see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-search-after.html}
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductAndProductModelSearchAfterCursor implements CursorInterface
{
    /** @var string|null */
    protected $searchAfterUniqueKey;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $fetchedItemsCount;

    /** @var array */
    protected $searchAfter;

    /** @var array */
    protected $initialSearchAfter;

    /** @var CursorableRepositoryInterface  */
    protected $productModelRepository;

    /** @var CursorableRepositoryInterface  */
    protected $productRepository;

    /** @var Client */
    protected $esClient;

    /** @var array */
    protected $esQuery;

    /** @var string */
    protected $indexType;

    /** @var array */
    protected $items;

    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $count;


    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $productRepository,
        CursorableRepositoryInterface $productModelRepository,
        array $esQuery,
        array $searchAfter = [],
        $indexType,
        $pageSize,
        $limit,
        $searchAfterUniqueKey = null
    ) {
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->esClient = $esClient;
        $this->esQuery = $esQuery;
        $this->indexType = $indexType;
        $this->pageSize = $pageSize;
        $this->limit = $limit;
        $this->searchAfter = $searchAfter;
        $this->initialSearchAfter = $this->searchAfter;
        $this->searchAfterUniqueKey = $searchAfterUniqueKey;
        $this->fetchedItemsCount = 0;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (false === next($this->items)) {
            $this->fetchedItemsCount += count($this->items);
            $this->items = $this->getNextItems($this->esQuery);
            reset($this->items);
        }
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
     * {@inheritdoc}
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-search-after.html
     */
    protected function getNextIdentifiers(array $esQuery): IdentifierResults
    {
        $identifiers = new IdentifierResults();

        $size = $this->limit > $this->pageSize ? $this->pageSize : $this->limit;
        if ($this->fetchedItemsCount + $size > $this->limit) {
            $size = $this->limit - $this->fetchedItemsCount;
        }
        $esQuery['size'] = $size;

        if (0 === $esQuery['size']) {
            return $identifiers;
        }

        if (!empty($this->searchAfter)) {
            $esQuery['search_after'] = $this->searchAfter;
        }

        $response = $this->esClient->search($this->indexType, $esQuery);
        $this->count = $response['hits']['total'];

        foreach ($response['hits']['hits'] as $hit) {
            $identifiers->add($hit['_source']['identifier'], $hit['_source']['document_type']);
        }

        $lastResult = end($response['hits']['hits']);

        if (false !== $lastResult) {
            $this->searchAfter = $lastResult['sort'];
        }

        return $identifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->searchAfter = $this->initialSearchAfter;
//        if (null !== $this->searchAfterUniqueKey) {
//            array_push($this->searchAfter, $this->indexType . '#' . $this->searchAfterUniqueKey);
//        }

        $this->fetchedItemsCount = 0;
        $this->items = $this->getNextItems($this->esQuery);
        reset($this->items);
    }

    /**
     * {@inheritdoc}
     */
    public function current()
    {
        if (null === $this->items) {
            $this->rewind();
        }

        if (empty($this->items)) {
            return null;
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
}
