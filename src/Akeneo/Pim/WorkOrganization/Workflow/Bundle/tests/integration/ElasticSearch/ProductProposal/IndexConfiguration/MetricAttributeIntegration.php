<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\tests\integration\ElasticSearch\ProductProposal\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration with metric data (float and integer values)
 * the metric research is consistent.
 */
class MetricAttributeIntegration extends AbstractProductProposalTestCase
{
    public function testLowerThanOperatorWithNumberValue()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                'query'         => '<'. 10,
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOperatorWithNumberValueWithScopableAttribute()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.ecommerce.fr_FR.base_data',
                'query'         => '<'. 10,
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, []);

        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.ecommerce.en_US.base_data',
                'query'         => '<'. 10,
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1']);
    }

    public function testLowerThanOperatorWithStringValue()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                'query'         => '<10',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOrEqualsOperatorWithNumberValue()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                'query'         => '<='. 10,
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testLowerThanOrEqualsOperatorWithStringValue()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                'query'         => '<=10',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5', 'product_6']);
    }

    public function testEqualsOperatorWithNumberValue()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                'query'         => 100.666,
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3']);
    }

    public function testEqualsOperatorWithStringValue()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                'query'         => '100.666',
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
                    'field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                ]
            ],
            [
                'query_string' => [
                    'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                    'query'         => 100.666,
                ],
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_2', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testNotEqualsOperatorWithStringValue()
    {
        $query = $this->buildQuery(
            [
                'exists' => [
                    'field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                ]
            ],
            [
                'query_string' => [
                    'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                    'query'         => '100.666',
                ],
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_2', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testGreaterThanOrEqualsOperatorWithNumberValue()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                'query'         => '>=' . 10,
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4']);
    }

    public function testGreaterThanOrEqualsOperatorWithStringValue()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                'query'         => '>=10',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4']);
    }

    public function testGreaterThanOperatorWithNumberValue()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                'query'         => '>' . 10,
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4']);
    }

    public function testGreaterThanOperatorWithStringValue()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                'query'         => '>=10',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_4']);
    }

    public function testEmptyOperator()
    {
        $query = $this->buildQuery(
            [],
            [
                'exists' => [
                    'field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_7']);
    }

    public function testNotEmptyOperator()
    {
        $query = $this->buildQuery(
            [
                'exists' => [
                    'field' => 'values.a_metric-metric.<all_channels>.<all_locales>.base_data',
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_2', 'product_3', 'product_4', 'product_5', 'product_6']
        );
    }

    /**
     * This method indexes dummy products in elastic search.
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
                    'a_metric-metric' => [
                        'ecommerce' => [
                            'fr_FR' => [
                                'base_data' => '10.0',
                                'data' => '10000',
                                'base_unit' => 'CELSIUS',
                                'unit' => 'CELSIUS',
                            ],
                            'en_US' => [
                                'base_data' => '5.0',
                                'data' => '5000',
                                'base_unit' => 'FAHRENHEIT',
                                'unit' => 'FAHRENHEIT',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'a_metric-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'base_data' => -1,
                                'base_unit' => 'KILOGRAM'
                            ]
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'a_metric-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'base_data' => '100.666',
                                'data' => '152',
                                'base_unit' => 'KILOGRAM'
                            ]
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'a_metric-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'base_data' => '25.89',
                                'base_unit' => 'KILOGRAM'
                            ]
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'a_metric-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'base_data' => '-3900',
                                'base_unit'  => 'KILOGRAM',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'a_metric-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' =>[
                                'base_data' => '7',
                                'base_unit'  => 'KILOGRAM',
                            ],
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
