<?php

namespace Akeneo\Pim\WorkOrganization\Workflow\Bundle\tests\integration\ElasticSearch\ProductProposal\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration with simple option (string) values
 * the option research is consistent.
 */
class OptionAttributeIntegration extends AbstractProductProposalTestCase
{
    public function testInListOperatorWithOptionValue()
    {
        $query = $this->buildQuery([
            'terms' => [
                'values.color-option.<all_channels>.<all_locales>' => ['black', 'yellow'],
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_3', 'product_6']);
    }

    public function testIsEmptyOperatorWithOptionValue()
    {
        $query = $this->buildQuery(
            [],
            [
                'exists' => [
                    'field' => 'values.color-option.<all_channels>.<all_locales>',
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
                'field' => 'values.color-option.<all_channels>.<all_locales>',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']);
    }

    public function testNotInListOperatorWithOptionValue()
    {
        $query = $this->buildQuery(
            [
                'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>']
            ],
            [
                'terms' => [
                    'values.color-option.<all_channels>.<all_locales>' => ['black', 'blue'],
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_6']);
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
        ];

        $this->indexDocuments($products);
    }
}
