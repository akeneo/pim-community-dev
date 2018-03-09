<?php

namespace PimEnterprise\Bundle\WorkflowBundle\tests\integration\ElasticSearch\ProductProposal\IndexConfiguration;

class ReferenceDataMultiSelectAttributeIntegration extends AbstractProductProposalTestCase
{
    public function testInListOperatorWithOptionValue()
    {
        $query = $this->buildQuery([
            'terms' => [
                'values.a_ref_data_multi_select-reference_data_options.<all_channels>.<all_locales>' => ['black', 'yellow'],
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            [
                'product_1',
                'product_2',
                'product_3',
                'product_6',
                'product_4',
                'product_5'
            ]
        );
    }

    public function testIsEmptyOperatorWithOptionValue()
    {
        $query = $this->buildQuery(
            [],
            [
                'exists' => [
                    'field' => 'values.a_ref_data_multi_select-reference_data_options.<all_channels>.<all_locales>',
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_7']);
    }

    public function testIsNotEmptyOperatorWithOptionValue()
    {
        $query = $this->buildQuery([
            'exists' => [
                'field' => 'values.a_ref_data_multi_select-reference_data_options.<all_channels>.<all_locales>',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']);
    }

    public function testNotInListOperatorWithOptionValue()
    {
        $query = $this->buildQuery(
            [
                'exists' => ['field' => 'values.a_ref_data_multi_select-reference_data_options.<all_channels>.<all_locales>']
            ],
            [
                'terms' => [
                    'values.a_ref_data_multi_select-reference_data_options.<all_channels>.<all_locales>' => ['black', 'blue'],
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_6']);
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
                    'a_ref_data_multi_select-reference_data_options' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['black']
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'a_ref_data_multi_select-reference_data_options' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['yellow', 'blue'],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'a_ref_data_multi_select-reference_data_options' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['black'],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'a_ref_data_multi_select-reference_data_options' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['blue', 'yellow'],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'a_ref_data_multi_select-reference_data_options' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['blue', 'black'],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'a_ref_data_multi_select-reference_data_options' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['yellow'],
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
