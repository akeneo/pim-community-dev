<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\ProductProposal\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration with number (float and integer) values
 * the number research is consistent.
 */
class NumberAttributeIntegration extends AbstractProductProposalTestCase
{
    public function testLowerThanOperatorWithNumberValue()
    {
        $query = $this->buildQuery([
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => '<'. 10,
                ]
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOperatorWithStringValue()
    {
        $query = $this->buildQuery([
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => '<10',
                ]
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOrEqualsOperatorWithNumberValue()
    {
        $query = $this->buildQuery([
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => '<='. 10,
                ]
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOrEqualsOperatorWithStringValue()
    {
        $query = $this->buildQuery([
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => '<=10',
                ]
            ]
        ]);
        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_5', 'product_6']);
    }

    public function testEqualsOperatorWithNumberValue()
    {
        $query = $this->buildQuery([
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => 100.666,
                ]
            ]
        ]);
        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3']);
    }

    public function testEqualsOperatorWithStringValue()
    {
        $query = $this->buildQuery([
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => '100.666',
                ]
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3']);
    }

    public function testNotEqualsOperatorWithNumberValue()
    {
        $query = $this->buildQuery(
            [
                'exists' => [
                    'field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                ]
            ],
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => 100.666,
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testNotEqualsOperatorWithStringValue()
    {
        $query = $this->buildQuery(
            [
                'exists' => [
                    'field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                ]
            ],
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => '100.666',
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testGreaterThanOrEqualsOperatorWithNumberValue()
    {
        $query = $this->buildQuery(
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => '>=' . 10,
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_3', 'product_4']);
    }

    public function testGreaterThanOrEqualsOperatorWithStringValue()
    {
        $query = $this->buildQuery(
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => '>=10',
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_3', 'product_4']);
    }

    public function testGreaterThanOperatorWithNumberValue()
    {
        $query = $this->buildQuery(
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => '>' . 10,
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4']);
    }

    public function testGreaterThanOperatorWithStringValue()
    {
        $query = $this->buildQuery(
            [
                'query_string' => [
                    'default_field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                    'query'         => '>10',
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4']);
    }

    public function testEmptyOperator()
    {
        $query = $this->buildQuery(
            [],
            [
                'exists' => [
                    'field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_7']);
    }

    public function testNotEmptyOperator()
    {
        $query = $this->buildQuery(
            [
                'exists' => [
                    'field' => 'values.box_quantity-decimal.<all_channels>.<all_locales>',
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']
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
