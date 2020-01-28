<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\ProductProposal\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration and some products indexed
 * the text research is consistent.
 */
class TextAttributeIntegration extends AbstractProductProposalTestCase
{
    public function testStartWithOperator()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.name-text.<all_channels>.<all_locales>',
                'query'         => 'an*',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5']);
    }

    public function testStartWithOperatorWithWhiteSpace()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field'       => 'values.name-text.<all_channels>.<all_locales>',
                'query'               => 'My\ product*',
            ],
        ]);


        $productsFound = $this->getSearchQueryResults($query);
        $this->assertDocument($productsFound, []);
    }

    public function testStartWithOperatorWithWhiteSpaceWithAttributeLocalizable()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field'       => 'values.name-text.<all_channels>.fr_FR',
                'query'               => 'My\ product*',
            ],
        ]);


        $productsFound = $this->getSearchQueryResults($query);
        $this->assertDocument($productsFound, []);

        $query = $this->buildQuery([
            'query_string' => [
                'default_field'       => 'values.name-text.<all_channels>.en_US',
                'query'               => 'My\ product*',
            ],
        ]);


        $productsFound = $this->getSearchQueryResults($query);
        $this->assertDocument($productsFound, ['product_1']);
    }

    public function testContainsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'values.name-text.<all_channels>.<all_locales>',
                            'query'         => '*Love*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_6', 'product_7', 'product_8']);
    }

    public function testContainsOperatorWithWhiteSpace()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'values.name-text.<all_channels>.<all_locales>',
                            'query'         => '*Love\\ this*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_6']);
    }

    public function testDoesNotContainOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'query_string' => [
                            'default_field' => 'values.name-text.<all_channels>.<all_locales>',
                            'query'         => '*Love*',
                        ],
                    ],
                    'filter' => [
                        'exists' => [
                            'field' => 'values.name-text.<all_channels>.<all_locales>',
                        ]
                    ]
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_5']);
    }

    public function testEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'values.name-text.<all_channels>.<all_locales>',
                            'query'         => 'I-love.dots',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_8']);
    }

    public function testNotEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'query_string' => [
                            'default_field' => 'values.name-text.<all_channels>.<all_locales>',
                            'query'         => 'I-love.dots',
                        ],
                    ],
                    'filter'   => [
                        'exists' => ['field' => 'values.name-text.<all_channels>.<all_locales>'],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_2', 'product_3', 'product_5', 'product_6', 'product_7']
        );
    }

    public function testEmptyOperator()
    {
        $query = [
                'query' => [
                    'bool' => [
                        'must_not' => [
                            'exists' => ['field' => 'values.name-text.<all_channels>.<all_locales>'],
                        ],
                    ],
                ],
            ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_4']);
    }

    public function testNotEmptyOperator()
    {
        $query = [
                'query' => [
                    'bool' => [
                        'filter' => [
                            'exists' => ['field' => 'values.name-text.<all_channels>.<all_locales>'],
                        ],
                    ],
                ],
            ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_2', 'product_3', 'product_5', 'product_6', 'product_7', 'product_8']
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
                    'name-text' => [
                        '<all_channels>' => [
                            'en_US' => 'My product',
                            'fr_FR' => 'Mon product',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Another product',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Yeah, love this name',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'And an uppercase NAME',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Love this product',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_7',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'I.love.dots',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_8',
                'values'     => [
                    'name-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'I-love.dots',
                        ],
                    ],
                ],
            ],
        ];

        $this->indexDocuments($products);
    }
}
