<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

/**
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimCatalogFamilyIntegration extends AbstractPimCatalogTestCase
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
            'sort'  => [
                [
                    'identifier' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
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
            'sort'  => [
                [
                    'identifier' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
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
            'sort'  => [
                [
                    'identifier' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
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
            'sort'  => [
                [
                    'identifier' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['sony-xvz', 'toshiba', 'carley-co', 'nike-flush']);
    }

    public function testSortLabelDescending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'family.labels.en_US' => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                    ],
                ],
                [
                    'family.code' => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['nike-flush', 'carley-co', 'toshiba', 'sony-xvz', 'product_3']
        );

        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'family.labels.fr_FR' => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                    ],
                ],
                [
                    'family.code' => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                    ],
                ],
                [
                    'identifier' => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['sony-xvz', 'toshiba', 'nike-flush', 'carley-co', 'product_3']
        );
    }

    public function testSortCodeDescending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'family.code' => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                    ],
                ],
                [
                    'identifier' => [
                        'order'   => 'DESC',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['sony-xvz', 'toshiba', 'nike-flush', 'carley-co', 'product_3']
        );
    }

    public function testSortLabelAscending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'family.labels.en_US' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                    ],
                ],
                [
                    'family.code' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                    ],
                ],
                [
                    'identifier' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['toshiba', 'sony-xvz', 'carley-co', 'nike-flush', 'product_3']
        );

        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'family.labels.fr_FR' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                    ],
                ],
                [
                    'family.code' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                    ],
                ],
                [
                    'identifier' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['carley-co', 'nike-flush', 'toshiba', 'sony-xvz', 'product_3']
        );
    }

    public function testSortCodeAscending()
    {
        $query = [
            'query' => [
                'match_all' => new \stdClass(),
            ],
            'sort'  => [
                [
                    'family.code' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                    ],
                ],
                [
                    'identifier' => [
                        'order'   => 'ASC',
                        'missing' => '_last',
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['sony-xvz', 'toshiba', 'carley-co', 'nike-flush', 'product_3']
        );
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
