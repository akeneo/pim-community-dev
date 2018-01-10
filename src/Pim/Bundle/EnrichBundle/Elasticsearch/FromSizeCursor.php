<?php

namespace Pim\Bundle\EnrichBundle\Elasticsearch;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;
use Pim\Bundle\CatalogBundle\Elasticsearch\AbstractCursor;
use Pim\Bundle\CatalogBundle\Elasticsearch\IdentifierResults;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Bounded cursor to iterate over items where a start and a limit are defined.
 * Internally, this is implemented with the from/size pagination.
 * {@see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-from-size.html}
 *
 * This cursor is dedicated to the search in the datagrid where we need to have 2 types of objects:
 * products and product models.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FromSizeCursor extends AbstractCursor implements CursorInterface
{
    /** @var array */
    protected $esQuery;

    /** @var string */
    protected $indexType;

    /** @var int */
    protected $pageSize;

    /** @var int */
    protected $initialFrom;

    /** @var int */
    protected $from;

    /** @var int */
    protected $limit;

    /** @var int */
    protected $to;

    /** @var int */
    protected $fetchedItemsCount;

    /**
     * @param Client                        $esClient
     * @param EntityManagerInterface        $entityManager
     * @param array                         $esQuery
     * @param string                        $indexType
     * @param int                           $pageSize
     * @param int                           $limit
     * @param int                           $from
     */
    public function __construct(
        Client $esClient,
        EntityManagerInterface $entityManager,
        array $esQuery,
        $indexType,
        $pageSize,
        $limit,
        $from = 0
    ) {
        $this->esClient = $esClient;
        $this->entityManager = $entityManager;
        $this->esQuery = $esQuery;
        $this->indexType = $indexType;
        $this->pageSize = $pageSize;
        $this->limit = $limit;
        $this->from = $from;
        $this->initialFrom = $from;
        $this->to = $this->from + $this->limit;
    }

    /**
     * {@inheritdoc}
     */
    public function next()
    {
        if (false === next($this->items)) {
            $this->from += count($this->items);
            $this->items = $this->getNextItems($this->esQuery);
            reset($this->items);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind()
    {
        $this->from = $this->initialFrom;
        $this->to = $this->from + $this->limit;
        $this->items = $this->getNextItems($this->esQuery);
        reset($this->items);
    }

    /**
     * {@inheritdoc}
     *
     * {@see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-from-size.html}
     */
    protected function getNextIdentifiers(array $esQuery): IdentifierResults
    {
        $size = ($this->to - $this->from) > $this->pageSize ? $this->pageSize : ($this->to - $this->from);
        $esQuery['size'] = $size;
        $identifiers = new IdentifierResults();

        if (0 === $esQuery['size']) {
            return $identifiers;
        }

        $sort = ['_uid' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;
        $esQuery['from'] = $this->from;

        $response = $this->esClient->search($this->indexType, $esQuery);
        $this->count = $response['hits']['total'];

        foreach ($response['hits']['hits'] as $hit) {
            $identifiers->add($hit['_source']['identifier'], $hit['_source']['document_type']);
        }

        return $identifiers;
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

        $rawProducts = $this->getRawProductFromIdentifier(
            $identifierResults->getProductIdentifiers()
        );
        $rawProductModels = $this->getRawProductModelFromIdentifier(
            $identifierResults->getProductModelIdentifiers()
        );
        $rawItems = array_merge($rawProducts, $rawProductModels);

        return $rawItems;
    }

    /**
     * {@inheritdoc}
     */
    public function getRawProductModelFromIdentifier(array $identifiers): array
    {
        if (0 === count($identifiers)) {
            return [];
        }

        $paramNames = array_map(function ($index) {
            return '?';
        }, range(0, count($identifiers) - 1));
        $sql = 'SELECT * FROM pim_catalog_product_model as product_model WHERE product_model.code IN ('. implode(',', $paramNames) .') AND product_model.product_type = \'product_model\'';

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute($identifiers);

        return $stmt->fetchAll();
    }

    /**
     * {@inheritdoc}
     */
    public function getRawProductFromIdentifier(array $identifiers): array
    {
        if (0 === count($identifiers)) {
            return [];
        }

        $paramNames = array_map(function ($index) {
            return '?';
        }, range(0, count($identifiers) - 1));
        $sql = 'SELECT * FROM pim_catalog_product as product WHERE product.identifier IN ('. implode(',', $paramNames) .') AND product.product_type IN (\'product\', \'variant_product\')';

        $stmt = $this->entityManager->getConnection()->prepare($sql);
        $stmt->execute($identifiers);

        return $stmt->fetchAll();
    }
}
