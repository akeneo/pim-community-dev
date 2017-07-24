<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch;

class ProductAndModelSearchQueryBuilder extends SearchQueryBuilder
{
    protected $attributeCodes = [];

    /**
     * Returns an Elastic search Query
     *
     * @param array $source
     *
     * @return array
     */
    public function getQuery(array $source = [])
    {
        if (empty($source)) {
            $source = ['identifier'];
        }
        $searchQuery = [
            '_source' => $source,
            'query'   => [],
        ];

        if (!empty($this->filterClauses)) {
            $searchQuery['query']['bool']['filter'] = $this->filterClauses;
        }

        if (!empty($this->mustNotClauses)) {
            $searchQuery['query']['bool']['must_not'] = $this->mustNotClauses;
        }

        if (!empty($this->shouldClauses)) {
            $searchQuery['query']['bool']['should'] = $this->shouldClauses;
            $searchQuery['query']['bool']['minimum_should_match'] = 1;
        }

        if (!empty($this->sortClauses)) {
            $searchQuery['sort'] = $this->sortClauses;
        }

        if (empty($searchQuery['query'])) {
            $searchQuery['query'] = new \stdClass();
        }

        // Add extra clause to do smart search (p&pm)
        if (empty($searchQuery['query']['bool']['filter'])) {
            $searchQuery['query']['bool']['filter'] = [];
        }
        $searchQuery['query']['bool']['filter'][] = [
            'terms' => ['owned_attributes' => $this->attributeCodes],
        ];

        return $searchQuery;
    }
}
