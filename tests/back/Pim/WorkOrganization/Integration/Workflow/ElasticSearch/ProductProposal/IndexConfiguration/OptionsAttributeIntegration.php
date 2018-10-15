<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\ProductProposal\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration with multi options (string[]) values
 * the options research is consistent.
 */
class OptionsAttributeIntegration extends AbstractProductProposalTestCase
{
    public function testInListOperatorWithOptionValue()
    {
        $query = $this->buildQuery([
            'terms' => [
                'values.colors-options.<all_channels>.<all_locales>' => ['black', 'yellow'],
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
                    'field' => 'values.colors-options.<all_channels>.<all_locales>',
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
                'field' => 'values.colors-options.<all_channels>.<all_locales>',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']);
    }

    public function testNotInListOperatorWithOptionValue()
    {
        $query = $this->buildQuery(
            [
                'exists' => ['field' => 'values.colors-options.<all_channels>.<all_locales>']
            ],
            [
                'terms' => [
                    'values.colors-options.<all_channels>.<all_locales>' => ['black', 'blue'],
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
                    'colors-options' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['black']
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'colors-options' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['yellow', 'blue'],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'colors-options' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['black'],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'colors-options' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['blue', 'yellow'],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'colors-options' => [
                        '<all_channels>' => [
                            '<all_locales>' => ['blue', 'black'],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'colors-options' => [
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
