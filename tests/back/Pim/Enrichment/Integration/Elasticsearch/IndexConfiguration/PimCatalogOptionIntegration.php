<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration with simple option (string) values
 * the option research is consistent.
 *
 * @author    Philippe Mossière <philippe.mossiere@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimCatalogOptionIntegration extends AbstractPimCatalogTestCase
{
    public function testInListOperatorWithOptionValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'values.color-option.<all_channels>.<all_locales>' => ['black', 'yellow'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_3', 'product_6']);
    }

    public function testIsEmptyOperatorWithOptionValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'values.color-option.<all_channels>.<all_locales>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_7', 'product_8']);
    }

    public function testIsNotEmptyOperatorWithOptionValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'values.color-option.<all_channels>.<all_locales>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']);
    }

    public function testNotInListOperatorWithOptionValue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'terms' => [
                            'values.color-option.<all_channels>.<all_locales>' => ['black', 'blue'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_6', 'product_7', 'product_8']);
    }

    public function testSortAscending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'values.color-option.<all_channels>.<all_locales>' => [
                        'order'   => 'asc',
                        'missing' => '_last',
                    ],
                    'identifier' => ['order' => 'asc'],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertSame(
            ['product_1', 'product_3', 'product_4', 'product_5', 'product_2', 'product_6', 'product_7', 'product_8'],
            $productsFound
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
                    'values.color-option.<all_channels>.<all_locales>' => [
                        'order'   => 'desc',
                        'missing' => '_last',
                    ],
                    'identifier' => ['order' => 'asc'],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertSame(
            ['product_2', 'product_6', 'product_4', 'product_5', 'product_1', 'product_3', 'product_7', 'product_8'],
            $productsFound
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function addDocuments()
    {
        $products = [
            [
                'identifier' => 'product_1',
                'values'     => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'black',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'yellow',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'black',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'yellow',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_7',
                'values'     => [],
            ],
            [
                'identifier' => 'product_8',
                'values'     => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => null,
                        ],
                    ],
                ],
            ],
        ];

        $this->indexDocuments($products);
    }
}
