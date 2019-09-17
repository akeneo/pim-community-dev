<?php

namespace Akeneo\Pim\Enrichment\Bundle\Cursor;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Cursor\Cursor;
use Akeneo\Tool\Component\StorageUtils\Cursor\CursorInterface;

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

        $sort = ['_id' => 'asc'];
        $esQuery['_source'] = ['id'];

        if (isset($esQuery['sort'])) {
            $sort = array_merge($esQuery['sort'], $sort);
        }

        $esQuery['sort'] = $sort;
        $esQuery['track_total_hits'] = true;

        if (!empty($this->searchAfter)) {
            $esQuery['search_after'] = $this->searchAfter;
        }

        $response = $this->esClient->search($esQuery);
        $this->count = $response['hits']['total']['value'];

        $identifiers = [];
        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = $hit['_source']['id'];
        }

        $lastResult = end($response['hits']['hits']);

        if (false !== $lastResult) {
            $this->searchAfter = $lastResult['sort'];
        }

        return $identifiers;
    }
}
