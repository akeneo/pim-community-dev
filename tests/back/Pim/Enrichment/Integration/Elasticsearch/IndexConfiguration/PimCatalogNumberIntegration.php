<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration with number (float and integer) values
 * the number research is consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogNumberIntegration extends AbstractPimCatalogTestCase
{
    public function testLowerThanOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => ['lt' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => ['lt' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOrEqualsOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => ['lte' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOrEqualsOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => ['lte' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_5', 'product_6']);
    }

    public function testEqualsOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => 100.666,
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3']);
    }

    public function testEqualsOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => '100.666',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3']);
    }

    public function testNotEqualsOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => 100.666,
                        ],
                    ],
                    'filter'   => [
                        'exists' => [
                            'field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testNotEqualsOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => '100.666',
                        ],
                    ],
                    'filter'   => [
                        'exists' => [
                            'field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testGreaterThanOrEqualsOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => ['gte' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_3', 'product_4']);
    }

    public function testGreaterThanOrEqualsOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => ['gte' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_3', 'product_4']);
    }

    public function testGreaterThanOperatorWithNumberValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => ['gt' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4']);
    }

    public function testGreaterThanOperatorWithStringValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_channels>.<all_locales>' => ['gt' => '10'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4']);
    }

    public function testEmptyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_7']);
    }

    public function testNotEmptyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testSortAscending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'values.box_quantity-decimal.<all_channels>.<all_locales>' => [
                        'order'   => 'asc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_2', 'product_5', 'product_6', 'product_1', 'product_4', 'product_3', 'product_7']
        );
    }

    public function testSortDescending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'values.box_quantity-decimal.<all_channels>.<all_locales>' => [
                        'order'   => 'desc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_3', 'product_4', 'product_1', 'product_6', 'product_5', 'product_2', 'product_7']
        );
    }

    /**
     * {@inheritdoc}
     *
     * A few information regarding the mapping of numbers and the data indexed in ES below.
     * We indexed data of different types:
     *  - integer as a php integer
     *  - integer as a php string
     *  - float as a php float
     *  - float as a php string
     *
     * What we want to test is that our ES queries are still correctly working despite those variations (eg, the
     * resilience of the ES indexing).
     *
     * In this precise case, some data might not be catched by our dynamic mapping, but ES is capable of casting them
     * and the queries are still working.
     */
    protected function addDocuments()
    {
        $products = [
            [
                'identifier' => 'product_1',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => 10.0,
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => -1,
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '100.666',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => 25.89,
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => '-3.9000',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_channels>' => [
                            '<all_locales>' => 7,
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_7',
                'values'     => [],
            ],
        ];

        $this->indexDocuments($products);
    }
}
