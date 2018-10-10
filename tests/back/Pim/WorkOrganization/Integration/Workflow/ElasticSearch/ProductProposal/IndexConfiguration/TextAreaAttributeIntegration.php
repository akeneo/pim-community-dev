<?php

namespace AkeneoTest\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\ProductProposal\IndexConfiguration;

/**
 * This integration tests checks that given an index configuration and some products indexed
 * the text area research is consistent.
 */
class TextAreaAttributeIntegration extends AbstractProductProposalTestCase
{
    public function testStartWithOperator()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.description-textarea.<all_channels>.<all_locales>',
                'query'         => 'an*',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_5']);
    }

    public function testStartWithOperatorWithAttributeLocalizableAndScopable()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.description-textarea.ecommerce.fr_FR',
                'query'         => 'an*',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, []);

        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.description-textarea.ecommerce.en_US',
                'query'         => 'an*',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2']);
    }

    public function testContainsOperator()
    {
        $query = $this->buildQuery([
            'query_string' => [
                'default_field' => 'values.description-textarea.<all_channels>.<all_locales>',
                'query'         => '*autre another*',
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, []);
    }

    public function testDoesNotContainOperator()
    {
        $query = $this->buildQuery(
            [
                'exists' => ['field' => 'values.description-textarea.<all_channels>.<all_locales>'],
            ],
            [
                'query_string' => [
                    'default_field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed',
                    'query'         => '*cool\\ product*',
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_3', 'product_4', 'product_5']);
    }

    public function testEqualsOperator()
    {
        $query = $this->buildQuery(
            [
                'query_string' => [
                    'default_field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed',
                    'query'         => 'yeah,\\ love\\ description',
                ]
            ]
        );
        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3']);
    }

    public function testNotEqualsOperator()
    {
        $query = $this->buildQuery(
            [
                'exists' => ['field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed'],
            ],
            [
                'query_string' => [
                    'default_field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed',
                    'query'         => 'yeah,\\ love\\ description',
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_4', 'product_5']);
    }

    public function testEmptyOperator()
    {
        $query = $this->buildQuery(
            [],
            [
                'exists' => ['field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed'],
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_6']);
    }

    public function testNotEmptyOperator()
    {
        $query = $this->buildQuery(
            [
                'exists' => ['field' => 'values.description-textarea.<all_channels>.<all_locales>.preprocessed'],
            ]
        );
        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_3', 'product_4', 'product_5']);
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
                        'ecommerce' => [
                            'en_US' => 'Another cool product, great!',
                            'fr_FR' => 'Un autre produit cool, super !',
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
