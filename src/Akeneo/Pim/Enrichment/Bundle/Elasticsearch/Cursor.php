<?php

declare(strict_types=1);

namespace Akeneo\Pim\Enrichment\Bundle\Elasticsearch;

use Akeneo\Pim\Enrichment\Component\Product\Query\ResultAwareInterface;
use Akeneo\Pim\Enrichment\Component\Product\Query\ResultInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

/**
 * Cursor to iterate over all items.
 * Internally, this is implemented with the search after pagination.
 * {@see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-search-after.html}
 *
 * This cursor is dedicated to the search of both products and product models.
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Cursor extends AbstractCursor implements CursorInterface, ResultAwareInterface
{
    private array $searchAfter = [];
    private ?ResultInterface $result = null;

    public function __construct(
        Client $esClient,
        ProductRepositoryInterface $productRepository,
        ProductModelRepositoryInterface $productModelRepository,
        array $esQuery,
        private int $pageSize
    ) {
        $this->esClient = $esClient;
        $this->productRepository = $productRepository;
        $this->productModelRepository = $productModelRepository;
        $this->esQuery = $esQuery;
    }

    /**
     * {@inheritdoc}
     */
    public function next(): void
    {
        if (false === next($this->items)) {
            $this->position += count($this->items);
            $this->items = $this->getNextItems($this->esQuery);
            reset($this->items);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rewind(): void
    {
        $this->searchAfter = [];
        $this->items = $this->getNextItems($this->esQuery);
        reset($this->items);
    }

    /**
     * {@inheritdoc}
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-search-after.html
     */
    protected function getNextIdentifiers(array $esQuery, ?int $size = null): IdentifierResults
    {
        $esQuery['size'] = $size ?? $this->pageSize;
        $identifiers = new IdentifierResults();

        if (0 === $esQuery['size']) {
            return $identifiers;
        }

        $sort = ['id' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;

        if (!empty($this->searchAfter)) {
            $esQuery['search_after'] = $this->searchAfter;
        }

        $response = $this->esClient->search($esQuery);
        $this->result = new ElasticsearchResult($response);

        foreach ($response['hits']['hits'] as $hit) {
            $identifiers->add($hit['_source']['identifier'], $hit['_source']['document_type'], $hit['_source']['id']);
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
    public function getResult(): ResultInterface
    {
        if (null === $this->result) {
            $this->getNextIdentifiers($this->esQuery);
        }

        return $this->result;
    }

    /**
     * Get the next items (hydrated from doctrine repository).
     *
     * PIM-10232: The quick-and-dirty fix here is to always return "pageSize" items (except of course when no more result is found).
     * Before the fix we could return less than the "pageSize" count, when ES and MySQL are de-synchronized (= there
     * is more result in ES than in MySQL).
     * Returning fewer results can cause some UoW issues (c.f. ticket)
     */
    protected function getNextItems(array $esQuery): array
    {
        $pageSize = $this->pageSize;

        $totalItems = [];
        $try = 0;
        do {
            $try++;

            $numberOfIdentifiersToFind = $pageSize - \count($totalItems);
            $identifierResults = $this->getNextIdentifiers($esQuery, $numberOfIdentifiersToFind);
            $newItems = $this->getNextItemsFromIdentifiers($identifierResults);
            $totalItems = \array_merge($totalItems, $newItems);
            if (\count($identifierResults->all()) < $numberOfIdentifiersToFind) {
                // There is no more result, we can stop the loop.
                break;
            }
        } while (\count($totalItems) < $pageSize && $try <= 5);

        return $totalItems;
    }
}
