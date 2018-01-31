<?php

namespace Pim\Bundle\CatalogBundle\Elasticsearch;

/**
 * TODO: /////////////////////////////////////////////////////////////////////////////////
 *
 * For now we call this class "Builder", because that's what it does. It builds iteratively the final ES query
 * and renders it as a php array when needed. Instances of this class are set in the filters (setQueryBuilder) so that
 * each filters has the possibility to add its own small condition to the query thanks to this SQB object.
 *
 * That is fine, and that's what we did in the previous version. But what we want to do instead is get rid of this
 * Search Query builder class and have each of the filters capable of modifying the data directly (because they are the
 * real builder parts in this architecture). So this class only becomes a data holder (which filter are allowed to
 * modify)and is capable of returning a full featured working query using getQuery(). This SearchQuery could easily
 * implement a more generic class which interface could support Doctrine ORM Queries, etc.. just something generic
 * enough.
 *
 * TODO: /////////////////////////////////////////////////////////////////////////////////
 *
 * This stateful class holds the multiple parts of an Elastic Search search query.
 *
 * In two different arrays, it keeps track of the conditions where:
 * - a property should be equal to a value (ES filter clause)
 * - a property should *not* be equal to a value (ES must_not clause)
 *
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl.html
 * @see https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-bool-query.html
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @internal This class is used by the ProductQueryBuilder to create an ES search query.
 */
class SearchQueryBuilder
{
    /** @var array */
    private $mustNotClauses = [];

    /** @var array */
    private $filterClauses = [];

    /** @var array */
    private $shouldClauses = [];

    /** @var array */
    private $sortClauses = [];

    /**
     * Adds a must_not clause to the query
     *
     * @param array $clause
     *
     * @return SearchQueryBuilder
     */
    public function addMustNot(array $clause)
    {
        $this->mustNotClauses[] = $clause;

        return $this;
    }

    /**
     * Adds a filter clause to the query
     *
     * @param array $clause
     *
     * @return SearchQueryBuilder
     */
    public function addFilter(array $clause)
    {
        $this->filterClauses[] = $clause;

        return $this;
    }

    /**
     * Adds a should clause to the query
     *
     * Warning: in the context of the PIM, a request containing a should clause is subject to a lot of side effects.
     * For instance, in one filter you want to filter on the property A with 2 possible values: A = 1 || A = 2
     * you could do the folowing:
     * `sqb->addShould([
     *  [
     *   'terms' => [
     *      'A' => 1
     *    ]
     *  ],
     *  [
     *   'terms' => [
     *      'A' => 1
     *    ]
     *  ]);`
     *
     * Later on, with another filter but the same sqb, you want to filter on property B: B = 1 || B =2
     * again, you would do the following:
     * `sqb->addShould([
     *  [
     *   'terms' => [
     *      'A' => 1
     *    ]
     *  ],
     *  [
     *   'terms' => [
     *      'A' => 1
     *    ]
     *  ]);`
     *
     * The resulting logical request looks like this: A = 1 || A = 2 || B = 1 || B = 2 where in fact what the user meant
     * was (A = 1 || A = 2) && (B = 1 || B = 2)
     *
     *
     * @param array $clause
     *
     * @return SearchQueryBuilder
     */
    public function addShould(array $clause)
    {
        $this->shouldClauses[] = $clause;

        return $this;
    }

    /**
     * Adds a sort clause to the query
     *
     * @param array $sort
     *
     * @return $this
     */
    public function addSort(array $sort)
    {
        $this->sortClauses = array_merge($this->sortClauses, $sort);

        return $this;
    }

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
            'query'   => [
                'constant_score' => [
                    'filter' => [],
                ],
            ],
        ];

        if (!empty($this->filterClauses)) {
            $searchQuery['query']['constant_score']['filter']['bool']['filter'] = $this->filterClauses;
        }

        if (!empty($this->mustNotClauses)) {
            $searchQuery['query']['constant_score']['filter']['bool']['must_not'] = $this->mustNotClauses;
        }

        if (!empty($this->shouldClauses)) {
            $searchQuery['query']['constant_score']['filter']['bool']['should'] = $this->shouldClauses;
            $searchQuery['query']['constant_score']['filter']['bool']['minimum_should_match'] = 1;
        }

        if (!empty($this->sortClauses)) {
            $searchQuery['sort'] = $this->sortClauses;
        }

        if (empty($searchQuery['query']['constant_score']['filter'])) {
            $searchQuery['query']['constant_score']['filter'] = new \stdClass();
        }

        return $searchQuery;
    }
}
