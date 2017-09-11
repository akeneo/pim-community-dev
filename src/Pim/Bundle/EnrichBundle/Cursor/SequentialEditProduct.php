<?php

namespace Pim\Bundle\EnrichBundle\Cursor;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\Cursor\Cursor;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Component\StorageUtils\Repository\CursorableRepositoryInterface;

/**
 * Iterator for sequential edit
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class SequentialEditProduct extends Cursor implements CursorInterface
{
    /**
     * {@inheritdoc}
     */
    public function __construct(
        Client $esClient,
        CursorableRepositoryInterface $repository,
        array $esQuery,
        $indexType,
        $pageSize
    ) {
        $esQuery['_source'] = ['id'];

        parent::__construct($esClient, $repository, $esQuery, $indexType, $pageSize);
    }

    /**
     * Get the next items (hydrated from doctrine repository).
     *
     * @param array $esQuery
     *
     * @return array
     */
    protected function getNextItems(array $esQuery)
    {
        $identifiers = $this->getNextIdentifiers($esQuery);
        if (empty($identifiers)) {
            return [];
        }

        return $identifiers;
    }

    /**
     * {@inheritdoc}
     *
     * @see https://www.elastic.co/guide/en/elasticsearch/reference/5.x/search-request-search-after.html
     */
    protected function getNextIdentifiers(array $esQuery)
    {
        $esQuery['size'] = $this->pageSize;

        if (0 === $esQuery['size']) {
            return [];
        }

        $sort = ['_uid' => 'asc'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;

        if (!empty($this->searchAfter)) {
            $esQuery['search_after'] = $this->searchAfter;
        }

        $response = $this->esClient->search($this->indexType, $esQuery);
        $this->count = $response['hits']['total'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            // if the id is an integer, we convert it from a string to integer
            $identifiers[] = $hit['_source']['id'] == intval($hit['_source']['id']) ?
                intval($hit['_source']['id']) :
                $hit['_source']['id'];
        }

        $lastResult = end($response['hits']['hits']);

        if (false !== $lastResult) {
            $this->searchAfter = $lastResult['sort'];
        }

        return $identifiers;
    }
}
