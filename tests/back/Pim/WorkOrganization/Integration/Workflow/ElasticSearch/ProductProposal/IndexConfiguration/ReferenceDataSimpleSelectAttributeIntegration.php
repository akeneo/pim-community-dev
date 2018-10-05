<?php

namespace AkeneoTest\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\ProductProposal\IndexConfiguration;

class ReferenceDataSimpleSelectAttributeIntegration extends AbstractProductProposalTestCase
{
    public function testInListOperatorWithRefDataOptionValue()
    {
        $query = $this->buildQuery([
            'terms' => [
                'values.a_ref_data_simple_select-reference_data_option.<all_channels>.<all_locales>' => ['black', 'yellow'],
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_3', 'product_6']);
    }

    public function testIsEmptyOperatorWithRefDataOptionValue()
    {
        $query = $this->buildQuery(
            [],
            [
                'exists' => [
                    'field' => 'values.a_ref_data_simple_select-reference_data_option.<all_channels>.<all_locales>',
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_7']);
    }

    public function testIsNotEmptyOperatorWithRefDataOptionValue()
    {
        $query = $this->buildQuery([
            'exists' => [
                'field' => 'values.a_ref_data_simple_select-reference_data_option.<all_channels>.<all_locales>',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']);
    }

    public function testNotInListOperatorWithRefDataOptionValue()
    {
        $query = $this->buildQuery(
            [
                'exists' => ['field' => 'values.a_ref_data_simple_select-reference_data_option.<all_channels>.<all_locales>']
            ],
            [
                'terms' => [
                    'values.a_ref_data_simple_select-reference_data_option.<all_channels>.<all_locales>' => ['black', 'blue'],
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_6']);
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    protected function addDocuments()
    {
        $products = [
            [
                'identifier' => 'product_1',
                'values'     => [
                    'a_ref_data_simple_select-reference_data_option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'black',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'a_ref_data_simple_select-reference_data_option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'yellow',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'a_ref_data_simple_select-reference_data_option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'black',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'a_ref_data_simple_select-reference_data_option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'a_ref_data_simple_select-reference_data_option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'a_ref_data_simple_select-reference_data_option' => [
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
        ];
        $this->indexDocuments($products);
    }
}
