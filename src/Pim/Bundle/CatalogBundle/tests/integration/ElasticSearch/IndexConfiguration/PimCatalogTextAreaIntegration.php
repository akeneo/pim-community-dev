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
                            'default_field' => 'values.description-text.<all_locales>.<all_channels>',
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
                            'default_field' => 'values.description-text.<all_locales>.<all_channels>.raw',
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
                            'default_field' => 'values.description-text.<all_locales>.<all_channels>.raw',
                            'query'         => '*cool\\ product*',
                        ],
                    ],
                    'filter'   => [
                        'exists' => ['field' => 'values.description-text.<all_locales>.<all_channels>'],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_3', 'product_4', 'product_5']);
    }

    public function testEqualsOperator()
    {
        $query = [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                'values.description-text.<all_locales>.<all_channels>.raw' => 'yeah, love description',
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
                                'values.description-text.<all_locales>.<all_channels>.raw' => 'yeah, love description',
                            ],
                        ],
                        'filter'   => [
                            'exists' => ['field' => 'values.description-text.<all_locales>.<all_channels>.raw'],
                        ],
                    ],
                ],
            ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_1', 'product_2', 'product_4', 'product_5']);
    }

    public function testEmptyOperator()
    {
        $query = [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'exists' => ['field' => 'values.description-text.<all_locales>.<all_channels>'],
                        ],
                    ],
                ],
            ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['product_6']);
    }

    public function testNotEmptyOperator()
    {
        $query = [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'exists' => ['field' => 'values.description-text.<all_locales>.<all_channels>'],
                        ],
                    ],
                ],
            ];

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
                    'values.description-text.<all_locales>.<all_channels>.raw' => [
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
                    'values.description-text.<all_locales>.<all_channels>.raw' => [
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
                'identifier' => 'product_1',
                'values'     => [
                    'description-text' => [
                        '<all_locales>' => [
                            '<all_channels>' => 'My product description',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'description-text' => [
                        '<all_locales>' => [
                            '<all_channels>' => 'Another cool product, great !',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'description-text' => [
                        '<all_locales>' => [
                            '<all_channels>' => 'Yeah, love description',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'description-text' => [
                        '<all_locales>' => [
                            '<all_channels>' => 'A better <h1>description</h1>',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'description-text' => [
                        '<all_locales>' => [
                            '<all_channels>' => 'And an uppercase DESCRIPTION',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
            ],
        ];

        $this->indexProducts($products);
    }
}
