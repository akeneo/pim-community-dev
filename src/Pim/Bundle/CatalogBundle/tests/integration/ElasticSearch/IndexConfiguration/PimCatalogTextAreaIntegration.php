<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Elasticsearch\IndexConfiguration;

use Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration\AbstractPimCatalogIntegration;

/**
 * This integration tests checks that given an index configuration and some products indexed
 * the text area research is consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogTextAreaIntegration extends AbstractPimCatalogIntegration
{
    public function testStartWithOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'description-text.raw',
                            'query'         => 'an*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_2', 'product_5']);
    }

    public function testContainsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'description-text.raw',
                            'query'         => '*My*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1']);
    }

    public function testDoesNotContainOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'query_string' => [
                            'default_field' => 'description-text.raw',
                            'query' => '*cool\ product*',
                        ],
                    ],
                    'filter'   => [
                        'exists' => ['field' => 'description-text'],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_3', 'product_4', 'product_5']);
    }

    public function testEqualsOperator()
    {
        $query =
            [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                'description-text.raw' => 'yeah, love description',
                            ],
                        ],
                    ],
                ],
            ]
        ;

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_3']);
    }

    public function testNotEqualsOperator()
    {
        $query =
            [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'term' => [
                                'description-text.raw' => 'yeah, love description',
                            ],
                        ],
                        'filter' => [
                            'exists' => ['field' => 'description-text.raw']
                        ]
                    ],
                ],
            ]
        ;

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_4', 'product_5']);
    }

    public function testEmptyOperator()
    {
        $query =
            [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'exists' => ['field' => 'description-text'],
                        ],
                    ],
                ],
            ]
        ;

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_6']);
    }

    public function testNotEmptyOperator()
    {
        $query =
            [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'exists' => ['field' => 'description-text'],
                        ],
                    ],
                ],
            ]
        ;

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_3', 'product_4', 'product_5']);
    }

    public function testSortAscending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'description-text.raw' => [
                        'order'   => 'asc',
                        'missing' => '_first',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_6', 'product_4', 'product_5', 'product_2', 'product_1', 'product_3']
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
                    'description-text.raw' => [
                        'order'   => 'desc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts(
            $productsFound,
            ['product_3', 'product_1', 'product_2', 'product_5', 'product_4', 'product_6']
        );
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    protected function addProducts()
    {
        $products = [
            [
                'identifier'       => 'product_1',
                'description-text' => 'My product description',
            ],
            [
                'identifier'       => 'product_2',
                'description-text' => 'Another cool product, great !',
            ],
            [
                'identifier'       => 'product_3',
                'description-text' => 'Yeah, love description',
            ],
            [
                'identifier'       => 'product_4',
                'description-text' => 'A better <h1>description</h1>',
            ],
            [
                'identifier'       => 'product_5',
                'description-text' => 'And an uppercase DESCRIPTION',
            ],
            [
                'identifier'       => 'product_6',
            ],
        ];

        $this->indexProducts($products);
    }
}
