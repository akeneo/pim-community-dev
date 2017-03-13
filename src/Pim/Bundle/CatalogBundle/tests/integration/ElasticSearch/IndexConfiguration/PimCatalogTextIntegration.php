<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration and some products indexed
 * the text research is consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogTextIndexConfigurationIntegration extends AbstractPimCatalogIntegration
{
    public function testStartWithOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-varchar',
                            'query'         => 'an*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_2', 'product_5']);
    }

    public function testStartWithOperatorWithWhiteSpace()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-varchar',
                            'query'         => 'My\ product*',
                            'split_on_whitespace' => true
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1']);
    }

    public function testContainsOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-varchar',
                            'query'         => '*Love*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_6', 'product_7', 'product_8']);
    }

    public function testContainsOperatorWithWhiteSpace()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'name-varchar',
                            'query'         => '*Love\\ this*',
                        ],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3', 'product_6']);
    }

    public function testDoesNotContainOperator()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'bool' => [
                    'must_not' => [
                        'query_string' => [
                            'default_field' => 'name-varchar',
                            'query'         => '*Love*',
                        ],
                    ],
                    'filter'   => [
                        'exists' => ['field' => 'name-varchar'],
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_5']);
    }

    public function testEqualsOperator()
    {
        $query = $this->createSearchQuery(
            [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                'name-varchar' => 'I-love.dots',
                            ],
                        ],
                    ],
                ],
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_8']);
    }

    public function testNotEqualsOperator()
    {
        $query = $this->createSearchQuery(
            [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'term' => [
                                'name-varchar' => 'I-love.dots',
                            ],
                        ],
                        'filter' => [
                            'exists' => ['field' => 'name-varchar'],
                        ],
                    ],
                ],
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_5', 'product_6', 'product_7']
        );
    }

    public function testEmptyOperator()
    {
        $query = $this->createSearchQuery(
            [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'exists' => ['field' => 'name-varchar'],
                        ],
                    ],
                ],
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_4']);
    }

    public function testNotEmptyOperator()
    {
        $query = $this->createSearchQuery(
            [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'exists' => ['field' => 'name-varchar'],
                        ],
                    ],
                ],
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_5', 'product_6', 'product_7', 'product_8']
        );
    }

    public function testSortAscending()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'name-varchar' => [
                        'order'   => 'asc',
                        'missing' => '_first',
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_4', 'product_5', 'product_2', 'product_8', 'product_7', 'product_6', 'product_1', 'product_3']
        );
    }

    public function testSortDescending()
    {
        $query = $this->createSearchQuery([
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'name-varchar' => [
                        'order'   => 'desc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_3', 'product_1', 'product_6', 'product_7', 'product_8', 'product_2', 'product_5', 'product_4']
        );
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    protected function addProducts()
    {
        $products = [
            [
                'sku-varchar'  => 'product_1',
                'name-varchar' => 'My product',
            ],
            [
                'sku-varchar'  => 'product_2',
                'name-varchar' => 'Another product',
            ],
            [
                'sku-varchar'  => 'product_3',
                'name-varchar' => 'Yeah, love this name',
            ],
            [
                'sku-varchar' => 'product_4',
            ],
            [
                'sku-varchar'  => 'product_5',
                'name-varchar' => 'And an uppercase NAME',
            ],
            [
                'sku-varchar'  => 'product_6',
                'name-varchar' => 'Love this product',
            ],
            [
                'sku-varchar'  => 'product_7',
                'name-varchar' => 'I.love.dots',
            ],
            [
                'sku-varchar'  => 'product_8',
                'name-varchar' => 'I-love.dots',
            ],
        ];

        $this->indexProducts($products);
    }
}
