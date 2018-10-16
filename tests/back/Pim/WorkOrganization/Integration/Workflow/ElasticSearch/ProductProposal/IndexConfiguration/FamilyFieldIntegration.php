<?php

namespace AkeneoTestEnterprise\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\ProductProposal\IndexConfiguration;

class FamilyFieldIntegration extends AbstractProductProposalTestCase
{
    public function testInListOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'family.code' => ['camcorders', 't-shirt'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['sony-xvz', 'toshiba', 'carley-co', 'nike-flush']);
    }

    public function testNotInList()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'terms' => [
                            'family.code' => ['t-shirt'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['sony-xvz', 'toshiba', 'product_3']);
    }

    public function testIsEmptyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'family.code',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3']);
    }

    public function testIsNotEmptyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'family.code',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['sony-xvz', 'toshiba', 'carley-co', 'nike-flush']);
    }

    /**
     * {@inheritdoc}
     */
    protected function addDocuments()
    {
        $products = [
            [
                'identifier' => 'sony-xvz',
                'family'     => [
                    'code'   => 'camcorders',
                    'labels' => [
                        'en_US' => 'The camcorders family',
                        'fr_FR' => 'La famille des cameras',
                    ],
                ],
            ],
            [
                'identifier' => 'toshiba',
                'family'     => [
                    'code'   => 'camcorders',
                    'labels' => [
                        'en_US' => 'The camcorders family',
                        'fr_FR' => 'La famille des cameras',
                    ],
                ],
            ],
            [
                'identifier' => 'nike-flush',
                'family'     => [
                    'code'   => 't-shirt',
                    'labels' => [
                        'en_US' => 'The T-Shirt family',
                        'fr_FR' => 'Des t-shirts',
                    ],
                ],
            ],
            [
                'identifier' => 'carley-co',
                'family'     => [
                    'code'   => 't-shirt',
                    'labels' => [
                        'en_US' => 'The T-Shirt family',
                        'fr_FR' => 'Des t-shirts',
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
            ],
        ];

        $this->indexDocuments($products);
    }
}
