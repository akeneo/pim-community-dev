<?php

namespace tests\integration\Pim\Bundle\CatalogBundle\Elasticsearch\IndexConfiguration;

use Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration\AbstractPimCatalogTestCase;

/**
 * This integration tests checks that given an index configuration and some products indexed
 * the text area research is consistent.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogTextAreaIntegration extends AbstractPimCatalogTestCase
{
    public function testStartWithOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'values.description-textarea.<all_channels>.<all_locales>',
                            'query'         => 'an*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5']);
    }

    public function testContainsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed',
                            'query'         => '*My*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1']);
    }

    public function testDoesNotContainOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'query_string' => [
                            'default_field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed',
                            'query'         => '*cool\\ product*',
                        ],
                    ],
                    'filter'   => [
                        'exists' => ['field' => 'values.description-textarea.<all_channels>.<all_locales>'],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_3', 'product_4', 'product_5']);
    }

    public function testEqualsOperator()
    {
        $query = [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'term' => [
                                'values.description-textarea.<all_channels>.<all_locales>.preprocessed' => 'yeah, love description',
                            ],
                        ],
                    ],
                ],
            ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3']);
    }

    public function testNotEqualsOperator()
    {
        $query = [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'term' => [
                                'values.description-textarea.<all_channels>.<all_locales>.preprocessed' => 'yeah, love description',
                            ],
                        ],
                        'filter'   => [
                            'exists' => ['field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed'],
                        ],
                    ],
                ],
            ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_4', 'product_5']);
    }

    public function testEmptyOperator()
    {
        $query = [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'exists' => ['field' => 'values.description-textarea.<all_channels>.<all_locales>'],
                        ],
                    ],
                ],
            ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_6']);
    }

    public function testNotEmptyOperator()
    {
        $query = [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'exists' => ['field' => 'values.description-textarea.<all_channels>.<all_locales>'],
                        ],
                    ],
                ],
            ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_3', 'product_4', 'product_5']);
    }

    public function testSortAscending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'values.description-textarea.<all_channels>.<all_locales>.preprocessed' => [
                        'order'   => 'asc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_4', 'product_5', 'product_2', 'product_1', 'product_3', 'product_6']
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
                    'values.description-textarea.<all_channels>.<all_locales>.preprocessed' => [
                        'order'   => 'desc',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_3', 'product_1', 'product_2', 'product_5', 'product_4', 'product_6']
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
                    'description-textarea' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'My product description',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'description-textarea' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Another cool product, great !',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'description-textarea' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Yeah, love description',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'description-textarea' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'A better <h1>description</h1>',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'description-textarea' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'And an uppercase DESCRIPTION',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
            ],
        ];

        $this->indexDocuments($products);
    }
}
