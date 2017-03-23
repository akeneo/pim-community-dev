<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration and some products indexed
 * the text area research is consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @author    Anaël Chardan <anael.chardan@akeneo.com>
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogNumberIntegration extends AbstractPimCatalogIntegration
{
    public function testLowerThanOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_locales>.<all_channels>' => ['lt' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOrEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_locales>.<all_channels>' => ['lte' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_5', 'product_6']);
    }

    public function testEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'values.box_quantity-decimal.<all_locales>.<all_channels>' => 100.666,
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3']);
    }

    public function testNotEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'values.box_quantity-decimal.<all_locales>.<all_channels>' => 100.666,
                        ],
                    ],
                    'filter'   => [
                        'exists' => [
                            'field' => 'values.box_quantity-decimal.<all_locales>.<all_channels>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_1', 'product_2', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testGreaterThanOrEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_locales>.<all_channels>' => ['gte' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_3', 'product_4']);
    }

    public function testGreaterThanOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'range' => [
                            'values.box_quantity-decimal.<all_locales>.<all_channels>' => ['gt' => 10],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_4']);
    }

    public function testEmptyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'values.box_quantity-decimal.<all_locales>.<all_channels>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_7']);
    }

    public function testNotEmptyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'values.box_quantity-decimal.<all_locales>.<all_channels>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
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
                    'values.box_quantity-decimal.<all_locales>.<all_channels>' => [
                        'order'   => 'asc',
                        'missing' => '_first',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_7', 'product_2', 'product_5', 'product_6', 'product_1', 'product_4', 'product_3']
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
                    'values.box_quantity-decimal.<all_locales>.<all_channels>' => [
                        'order'   => 'desc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_3', 'product_4', 'product_1', 'product_6', 'product_5', 'product_2', 'product_7']
        );
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    protected function addProducts()
    {
        $products = [
            [
                'identifier' => 'product_1',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_locales>' => [
                            '<all_channels>' => '10.0',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_locales>' => [
                            '<all_channels>' => 1,
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_locales>' => [
                            '<all_channels>' => '100.666',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_locales>' => [
                            '<all_channels>' => 25.89,
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_locales>' => [
                            '<all_channels>' => '3.9000',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'box_quantity-decimal' => [
                        '<all_locales>' => [
                            '<all_channels>' => 7,
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_7',
                'values'     => [],
            ],
        ];

        $this->indexProducts($products);
    }
}
