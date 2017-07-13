<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimCatalogProductModelIntegration extends AbstractPimCatalogIntegration
{
    const PRODUCT_MODEL_DOCUMENT_TYPE = 'pim_catalog_product_model_parent';

    public function testDefaultDisplay()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    public function testSearchTshirtInDescription()
    {
        $this->markTestIncomplete('Ask delphine about where description attribute is.');
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

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, ['model-tshirt-red', 'model-tshirt-unique']);
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

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, [
            'model-tshirt-red',
            'model-tshirt-unique-color',
            'model-tshirt-unique-size-red',
            'running-shoes-s-red',
            'running-shoes-m-red',
            'running-shoes-l-red',
        ]);
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

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, ['model-tshirt-grey', 'model-hat']);
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

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, [
            'model-tshirt-blue',
            'model-tshirt-unique-size-blue',
            'running-shoes-s-blue',
            'running-shoes-m-blue',
            'running-shoes-l-blue',
            'watch',
        ]);
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

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts(
            $productsFound,
            [
                'tshirt-grey-s',
                'tshirt-blue-s',
                'tshirt-red-s',
                'tshirt-unique-color-s',
                'model-running-shoes-s',
                'biker-jacket-leather-s',
                'biker-jacket-polyester-s',
            ]
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

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts(
            $productsFound,
            [
                'tshirt-grey-m',
                'tshirt-red-m',
                'tshirt-blue-m',
                'tshirt-unique-color-m',
                'hat-m',
                'model-running-shoes-m',
                'biker-jacket-leather-m',
                'biker-jacket-polyester-m',
            ]
        );
    }

    public function testSearchColorGreyAndSizeS()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'has_parent' => [
                                'type'  => 'pim_catalog_product_model_parent_1',
                                'query' => [
                                    'terms' => [
                                        'values.color-option.<all_channels>.<all_locales>' => ['grey'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'terms' => [
                                'values.size-option.<all_channels>.<all_locales>' => ['s'],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, ['tshirt-grey-s']);
    }

    public function testSearchColorGreyAndSizeM()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'has_parent' => [
                                'type'  => 'pim_catalog_product_model_parent_1',
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

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, ['tshirt-grey-m', 'hat-m']);
    }

    public function testSearchSizeMAndGrandParentColorWhite()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'has_parent' => [
                                'type'  => 'pim_catalog_product_model_parent_1',
                                'query' => [
                                    'has_parent' => [
                                        'type'  => 'pim_catalog_product_model_parent_2',
                                        'query' => [
                                            'terms' => [
                                                'values.color-option.<all_channels>.<all_locales>' => ['white'],
                                            ],
                                        ],
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

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, ['biker-jacket-polyester-m', 'biker-jacket-leather-m']);
    }

    public function testSearchColorGreyAndDescriptionTshirt()
    {
        $this->markTestIncomplete('Not done');
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, ['model-tshirt-grey']);
    }

    /** @group todo */
    public function testSearchMaterialCotton()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'values.material-option.<all_channels>.<all_locales>' => ['cotton'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts(
            $productsFound,
            [
                'model-tshirt-grey',
                'model-tshirt-red',
                'model-tshirt-unique-color',
                'model-tshirt-unique-size',
            ]
        );
    }

    public function testSearchMaterialLeather()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'values.material-option.<all_channels>.<all_locales>' => ['leather'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_2']
        );

        $this->assertProducts(
            $productsFound,
            [
                'model-running-shoes',
                'model-biker-jacket-leather',
            ]
        );
    }

    // Do more complex use cases
    // - Where color == grey and name == tshirt (Search on a model and one property of his parent)

    /**
     * {@inheritdoc}
     */
    protected function addProducts()
    {
        $productModels = [
            // simple tshirt
            [
                'identifier' => 'model-tshirt',
                'type'       => 'PimCatalogProductModel',
                'level'      => 2,
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
            ],

            // Tshirt model level-1 (varying on color)
            [
                'identifier'    => 'model-tshirt-grey',
                'type'          => 'PimCatalogProductModel',
                'parent'        => 'model-tshirt',
                'root_ancestor' => 'model-tshirt',
                'level'         => 1,
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
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
                    'material-option'    => [
                        '<all_channels>' => [
                            '<all_locales>' => 'cotton',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'model-tshirt-blue',
                'type'          => 'PimCatalogProductModel',
                'parent'        => 'model-tshirt',
                'root_ancestor' => 'model-tshirt',
                'level'         => 1,
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
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
                    'material-blue'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'model-tshirt-red',
                'type'          => 'PimCatalogProductModel',
                'parent'        => 'model-tshirt',
                'root_ancestor' => 'model-tshirt',
                'level'         => 1,
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
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
                    'material-option'    => [
                        '<all_channels>' => [
                            '<all_locales>' => 'cotton',
                        ],
                    ],
                ],
            ],

            // Tshirt unique color model
            [
                'identifier'    => 'model-tshirt-unique-color',
                'type'          => 'PimCatalogProductModel',
                'parent'        => 'model-tshirt-unique-color',
                'root_ancestor' => 'model-tshirt-unique-color',
                'level'         => 1,
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'image-media'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-rockstar.jpg',
                        ],
                    ],
                    'color-option'    => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                    'material-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'cotton',
                        ],
                    ],
                ],
            ],

            // Hats model
            [
                'identifier'    => 'model-hat',
                'type'          => 'PimCatalogProductModel',
                'parent'        => 'model-hat',
                'root_ancestor' => 'model-hat',
                'level'         => 1,
                'family'        => [
                    'code'   => 'hats',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'values'        => [
                    'color-option'    => [
                        '<all_channels>' => [
                            '<all_locales>' => 'grey',
                        ],
                    ],
                    'material-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'wool',
                        ],
                    ],
                ],
            ],

            // Tshirt unique size model
            [
                'identifier'    => 'model-tshirt-unique-size',
                'type'          => 'PimCatalogProductModel',
                'parent'        => 'model-tshirt-unique-size',
                'root_ancestor' => 'model-tshirt-unique-size',
                'level'         => 1,
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'image-media'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-unique-size.jpg',
                        ],
                    ],
                    'size-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'u',
                        ],
                    ],
                    'material-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'cotton',
                        ],
                    ],
                ],
            ],

            // Running shoes
            [
                'identifier' => 'model-running-shoes',
                'type'       => 'PimCatalogProductModel',
                'level'      => 2,
                'family'     => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'     => [
                    'material-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'model-running-shoes-s',
                'type'          => 'PimCatalogProductModel',
                'level'         => 1,
                'parent'        => 'model-running-shoes',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'model-running-shoes-m',
                'type'          => 'PimCatalogProductModel',
                'level'         => 1,
                'parent'        => 'model-running-shoes',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'model-running-shoes-l',
                'type'          => 'PimCatalogProductModel',
                'level'         => 1,
                'parent'        => 'model-running-shoes',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],

            // Biker jacket
            [
                'identifier' => 'model-biker-jacket',
                'type'       => 'PimCatalogProductModel',
                'level'      => 2,
                'family'     => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'     => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'model-biker-jacket-leather',
                'type'          => 'PimCatalogProductModel',
                'level'         => 1,
                'parent'        => 'model-biker-jacket',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'material-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'model-biker-jacket-polyester',
                'type'          => 'PimCatalogProductModel',
                'level'         => 1,
                'parent'        => 'model-biker-jacket',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'material-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
                        ],
                    ],
                ],
            ],

        ];

        $productVariants = [
            // tshirt variants (level 2: varying on color and size)
            [
                'identifier'    => 'tshirt-grey-s',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-grey',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided grey S',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-grey-m',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-grey',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided grey m',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-grey-l',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-grey',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided grey L',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-grey-xl',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-grey',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided grey XL',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'tshirt-blue-s',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-blue',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided blue S',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-blue-m',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-blue',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided blue M',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-blue-l',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-blue',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided blue L',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-blue-xl',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-blue',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided blue XL',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'tshirt-red-s',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-red',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided red S',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-red-m',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-red',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided red M',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-red-l',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-red',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided red L',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-red-xl',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-red',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided red XL',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            // T-shirt: size
            [
                'identifier'    => 'tshirt-unique-color-s',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-color',
                'root_ancestor' => 'model-tshirt-unique-color',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-unique-color-m',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-color',
                'root_ancestor' => 'model-tshirt-unique-color',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif M',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-unique-color-l',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-color',
                'root_ancestor' => 'model-tshirt-unique-color',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif L',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'tshirt-unique-color-xl',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-color',
                'root_ancestor' => 'model-tshirt-unique-color',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif XL',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            // Watch
            [
                'identifier'    => 'watch',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'watch',
                'root_ancestor' => 'watch',
                'family'        => [
                    'code'   => 'watch',
                    'labels' => [
                        'fr_FR' => 'La montre unique',
                    ],
                ],
                'values'        => [
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
                'identifier'    => 'hat-m',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-hat',
                'root_ancestor' => 'model-hat',
                'family'        => [
                    'code'   => 'hats',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Braided hat M',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'hat-l',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-hat',
                'root_ancestor' => 'model-hat',
                'family'        => [
                    'code'   => 'hats',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Braided hat L',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],

            // Tshirt unique size model
            [
                'identifier'    => 'model-tshirt-unique-size-blue',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-size',
                'root_ancestor' => 'model-tshirt-unique-size',
                'level'         => 0,
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size blue',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'model-tshirt-unique-size-red',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-size',
                'root_ancestor' => 'model-tshirt-unique-size',
                'level'         => 0,
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size red',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'model-tshirt-unique-size-yellow',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-size',
                'root_ancestor' => 'model-tshirt-unique-size',
                'level'         => 0,
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size yellow',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'yellow',
                        ],
                    ],
                ],
            ],

            // Running shoes
            [
                'identifier'    => 'running-shoes-s-white',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-running-shoes-s',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'running-shoes-s-blue',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-running-shoes-s',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'running-shoes-s-red',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-running-shoes-s',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'running-shoes-m-white',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-running-shoes-m',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'running-shoes-m-blue',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-running-shoes-m',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'running-shoes-m-red',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-running-shoes-m',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'running-shoes-l-white',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-running-shoes-l',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'running-shoes-l-blue',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-running-shoes-l',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'running-shoes-l-red',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-running-shoes-l',
                'root_ancestor' => 'model-running-shoes',
                'family'        => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                ],
            ],

            // Biker
            [
                'identifier'    => 'biker-jacket-leather-s',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-leather',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'biker jacket polyester s',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'biker-jacket-leather-m',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-leather',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'biker jacket polyester m',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'biker-jacket-leather-l',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-leather',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'biker jacket polyester l',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'biker-jacket-polyester-s',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-polyester',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'biker jacket polyester s',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'biker-jacket-polyester-m',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-polyester',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'biker jacket polyester m',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'biker-jacket-polyester-l',
                'type'          => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-polyester',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'biker jacket polyester l',
                        ],
                    ],
                    'size-option'      => [
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
}
