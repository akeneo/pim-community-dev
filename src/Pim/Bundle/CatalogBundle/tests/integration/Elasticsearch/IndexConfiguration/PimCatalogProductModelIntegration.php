<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimCatalogProductModelIntegration extends AbstractPimCatalogIntegration
{
    const PRODUCT_MODEL_DOCUMENT_TYPE = 'pim_catalog_product_model';

    public function testDefaultDisplay()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testSearchTshirtInDescription()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'query_string' => [
                                'default_field' => 'values.description-text.<all_channels>.<all_locales>',
                                'query'         => '*T-shirt*',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query, [self::PRODUCT_MODEL_DOCUMENT_TYPE]);

        $this->assertProducts($productsFound, ['model-tshirt-level-0', 'model-tshirt-unique-level-0']);
    }

    public function testSearchColorRed()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'values.color-option.<all_channels>.<all_locales>' => ['red'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query, [self::PRODUCT_MODEL_DOCUMENT_TYPE]);

        $this->assertProducts($productsFound, ['model-tshirt-level-1-red', 'model-tshirt-unique-level-0']);
    }

    public function testSearchColorGrey()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query, [self::PRODUCT_MODEL_DOCUMENT_TYPE]);

        $this->assertProducts($productsFound, ['model-tshirt-level-1-grey', 'model-hat-level-0']);
    }

    public function testSearchColorBlue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'values.color-option.<all_channels>.<all_locales>' => ['blue'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query, [self::PRODUCT_MODEL_DOCUMENT_TYPE]);

        $this->assertProducts($productsFound, ['model-tshirt-level-1-blue', 'watch']);
    }

    public function testSearchSizeS()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'values.size-option.<all_channels>.<all_locales>' => ['s'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query, [self::PRODUCT_MODEL_DOCUMENT_TYPE]);

        $this->assertProducts(
            $productsFound,
            ['tshirt-level-2-grey-s', 'tshirt-level-2-blue-s', 'tshirt-level-2-red-s', 'tshirt-uniq-color-size-s']
        );
    }

    public function testSearchSizeM()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'values.size-option.<all_channels>.<all_locales>' => ['m'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query, [self::PRODUCT_MODEL_DOCUMENT_TYPE]);

        $this->assertProducts(
            $productsFound,
            [
                'tshirt-level-2-grey-m',
                'tshirt-level-2-blue-m',
                'tshirt-level-2-red-m',
                'tshirt-uniq-color-size-m',
                'hat-m',
            ]
        );
    }

    public function testSearchColorGreyAndSizeM()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'has_parent' => [
                                'type'  => 'pim_catalog_product_model',
                                'query' => [
                                    'terms' => [
                                        'values.color-option.<all_channels>.<all_locales>' => ['grey'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'terms' => [
                                'values.size-option.<all_channels>.<all_locales>' => ['m'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query, [self::PRODUCT_MODEL_DOCUMENT_TYPE]);

        $this->assertProducts($productsFound, ['tshirt-level-2-grey-m', 'hat-m']);
    }

    /**
     * {@inheritdoc}
     */
    protected function addProducts()
    {
        $productModels = [
            // root model that does not exist in DB
            // used to referenced simple products (watch) that do not have
            // any product model associated.
            // TODO: Find another way
            [
                'identifier' => 'root-pm',
            ],
            // simple tshirt - level 0
            [
                'identifier' => 'model-tshirt-level-0',
                'level'      => 0,
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a round neck Divided',
                        ],
                    ],
                ],
            ],

            // Tshirt model level-1 (varying on color)
            [
                'identifier' => 'model-tshirt-level-1-grey',
                'level'      => 1,
                //                'parent'    => 'model-tshirt-level-0',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'color-option'       => [
                        '<all_channels>' => [
                            '<all_locales>' => 'grey',
                        ],
                    ],
                    'main_picture-media' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-grey.jpg',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'model-tshirt-level-1-blue',
                'level'      => 1,
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'color-option'       => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                    'main_picture-media' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-blue.jpg',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'model-tshirt-level-1-red',
                'level'      => 1,
                //                'parent'    => 'model-tshirt-level-0',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'color-option'       => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                    'main_picture-media' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-red.jpg',
                        ],
                    ],
                ],
            ],

            // Tshirt unique model
            [
                'identifier' => 'model-tshirt-unique-level-0',
                'level'      => 0,
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-rockstar.jpg',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                ],
            ],

            // Hats model
            [
                'identifier' => 'model-hat-level-0',
                'level'      => 0,
                'family'     => [
                    'code'   => 'hats',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Braided hat',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'grey',
                        ],
                    ],
                ],
            ],
        ];

        $productVariants = [
            // tshirt variants (level 2: varying on color and size)
            [
                'identifier' => 'tshirt-level-2-grey-s',
                'parent'     => 'model-tshirt-level-1-grey',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-level-2-grey-m',
                'parent'     => 'model-tshirt-level-1-grey',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-level-2-grey-l',
                'parent'     => 'model-tshirt-level-1-grey',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-level-2-grey-xl',
                'parent'     => 'model-tshirt-level-1-grey',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            [
                'identifier' => 'tshirt-level-2-blue-s',
                'parent'     => 'model-tshirt-level-1-blue',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-level-2-blue-m',
                'parent'     => 'model-tshirt-level-1-blue',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-level-2-blue-l',
                'parent'     => 'model-tshirt-level-1-blue',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-level-2-blue-xl',
                'parent'     => 'model-tshirt-level-1-blue',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            [
                'identifier' => 'tshirt-level-2-red-s',
                'parent'     => 'model-tshirt-level-1-red',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-level-2-red-m',
                'parent'     => 'model-tshirt-level-1-red',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-level-2-red-l',
                'parent'     => 'model-tshirt-level-1-red',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-level-2-red-xl',
                'parent'     => 'model-tshirt-level-1-red',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            // T-shirt: size
            [
                'identifier' => 'tshirt-uniq-color-size-s',
                'parent'     => 'model-tshirt-unique-level-0',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-uniq-color-size-m',
                'parent'     => 'model-tshirt-unique-level-0',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-uniq-color-size-l',
                'parent'     => 'model-tshirt-unique-level-0',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-uniq-color-size-xl',
                'parent'     => 'model-tshirt-unique-level-0',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            // Watch
            [
                'identifier' => 'watch',
                'parent'     => 'root-pm',
                'family'     => [
                    'code'   => 'watch',
                    'labels' => [
                        'fr_FR' => 'La montre unique',
                    ],

                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Metal watch blue/white striped',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
            ],

            // Hats variants (varying on size)
            [
                'identifier' => 'hat-m',
                'parent'     => 'model-hat-level-0',
                'family'     => [
                    'code'   => 'hats',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'hat-l',
                'parent'     => 'model-hat-level-0',
                'family'     => [
                    'code'   => 'hats',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'values'     => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
        ];

        $this->indexProductModels($productModels);
        $this->indexProducts($productVariants);
    }

    private function indexProductModels($productVariants)
    {
        foreach ($productVariants as $product) {
            $this->esClient->index(self::PRODUCT_MODEL_DOCUMENT_TYPE, $product['identifier'], null, $product);
        }

        $this->esClient->refreshIndex();
    }
}
