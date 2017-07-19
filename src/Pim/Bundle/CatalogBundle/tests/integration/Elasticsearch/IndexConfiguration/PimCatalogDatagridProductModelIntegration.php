<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * Search use cases of products and models in a "smart datagrid way".
 * It returns either products or models depending on where is information is stored.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimCatalogDatagridProductModelIntegration extends AbstractPimCatalogProductModelIntegration
{
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

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts(
            $productsFound,
            [
                'model-tshirt',
                'model-tshirt-unique-color',
                'model-tshirt-unique-size',
            ]
        );
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
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts($productsFound, [
            'model-tshirt-red',
            'model-tshirt-unique-color',
            'tshirt-unique-size-red',
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
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
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
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts($productsFound, [
            'model-tshirt-blue',
            'tshirt-unique-size-blue',
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
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
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
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
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
                    'minimum_should_match' => 1,
                    'should'               => [
                        [
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
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'terms' => [
                                                    'values.size-option.<all_channels>.<all_locales>' => ['s'],
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
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
                                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
                                                        ],
                                                    ],
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
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts($productsFound, ['tshirt-grey-s']);
    }

    public function testSearchColorGreyAndSizeM()
    {
        $query = [
            'query' => [
                'bool' => [
                    'minimum_should_match' => 1,
                    'should'               => [
                        [
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
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'terms' => [
                                                    'values.size-option.<all_channels>.<all_locales>' => ['m'],
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
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
                                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
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
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts($productsFound, ['tshirt-grey-m', 'hat-m']);
    }

    /**
     * Search for a model parent 1 in its values and the value of his parent.
     */
    public function testSearchColorGreyAndDescriptionTshirt()
    {
        $query = [
            'query' => [
                'bool' => [
                    'minimum_should_match' => 1,
                    'should'               => [
                        // Color in level 1 - Description in level 2
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_2',
                                            'query' => [
                                                'query_string' => [
                                                    'default_field' => 'values.description-text.<all_channels>.<all_locales>',
                                                    'query'         => '*T-shirt*',
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // Color and description in level 1
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'query_string' => [
                                                    'default_field' => 'values.description-text.<all_channels>.<all_locales>',
                                                    'query'         => '*T-shirt*',
                                                ],
                                            ],
                                        ],
                                    ],
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
                                ],
                            ],
                        ],

                        // Color and Description in level product
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'query_string' => [
                                            'default_field' => 'values.description-text.<all_channels>.<all_locales>',
                                            'query'         => '*T-shirt*',
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // Color in level product and description in level 1
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'query_string' => [
                                                    'default_field' => 'values.description-text.<all_channels>.<all_locales>',
                                                    'query'         => '*T-shirt*',
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // Color in level product and description in level 2
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'has_parent' => [
                                                    'type'  => 'pim_catalog_product_model_parent_2',
                                                    'query' => [
                                                        'query_string' => [
                                                            'default_field' => 'values.description-text.<all_channels>.<all_locales>',
                                                            'query'         => '*T-shirt*',
                                                        ],

                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // Color and description in level 2
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'has_parent' => [
                                                    'type'  => 'pim_catalog_product_model_parent_2',
                                                    'query' => [
                                                        'query_string' => [
                                                            'default_field' => 'values.description-text.<all_channels>.<all_locales>',
                                                            'query'         => '*T-shirt*',
                                                        ],

                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'has_parent' => [
                                                    'type'  => 'pim_catalog_product_model_parent_2',
                                                    'query' => [
                                                        'terms' => [
                                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts($productsFound, ['model-tshirt-grey']);
    }

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
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
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
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts(
            $productsFound,
            [
                'model-running-shoes',
                'model-biker-jacket-leather',
            ]
        );
    }

    /**
     * Is not part of any use case but a proof of concept regarding the query of an attribute which is hold by the
     * model_parent_2 (grand father).
     */
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
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts($productsFound, ['biker-jacket-polyester-m', 'biker-jacket-leather-m']);
    }

    public function testSearchNotColorGrey()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'values.color-option.<all_channels>.<all_locales>' => 'grey',
                        ],
                    ],
                    'filter'   => [
                        'exists' => [
                            'field' => 'values.color-option.<all_channels>.<all_locales>',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts(
            $productsFound,
            [
                'model-tshirt-blue',
                'model-tshirt-red',
                'model-tshirt-unique-color',
                'watch',
                'tshirt-unique-size-blue',
                'tshirt-unique-size-red',
                'tshirt-unique-size-yellow',
                'running-shoes-s-white',
                'running-shoes-s-blue',
                'running-shoes-s-red',
                'running-shoes-m-white',
                'running-shoes-m-blue',
                'running-shoes-m-red',
                'running-shoes-l-white',
                'running-shoes-l-blue',
                'running-shoes-l-red',
                'model-biker-jacket',
            ]
        );
    }

    public function testSearchNotColorGreyAndSizeS()
    {
        $query = [
            'query' => [
                'bool' => [
                    'should' => [
                        // Color level product & Size level parent 1
                        [
                            'bool' => [
                                'must_not' => [
                                    'term' => [
                                        'values.color-option.<all_channels>.<all_locales>' => 'grey',
                                    ],
                                ],
                                'filter'   => [
                                    [
                                        'exists' => [
                                            'field' => 'values.color-option.<all_channels>.<all_locales>',
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'term' => [
                                                    'values.size-option.<all_channels>.<all_locales>' => 's',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // Size level product & Color level parent 1
                        [
                            'bool' => [
                                'must_not' => [
                                    'has_parent' => [
                                        'type'  => 'pim_catalog_product_model_parent_1',
                                        'query' => [
                                            'term' => [
                                                'values.color-option.<all_channels>.<all_locales>' => 'grey',
                                            ],
                                        ],
                                    ],
                                ],
                                'filter'   => [
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'exists' => [
                                                    'field' => 'values.color-option.<all_channels>.<all_locales>',
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'term' => [
                                            'values.size-option.<all_channels>.<all_locales>' => 's',
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // Size product level & color model parent 2
                        [
                            'bool' => [
                                'must_not' => [
                                    'has_parent' => [
                                        'type'  => 'pim_catalog_product_model_parent_1',
                                        'query' => [
                                            'has_parent' => [
                                                'type'  => 'pim_catalog_product_model_parent_2',
                                                'query' => [
                                                    'term' => [
                                                        'values.color-option.<all_channels>.<all_locales>' => 'grey',
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'filter'   => [
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'has_parent' => [
                                                    'type'  => 'pim_catalog_product_model_parent_2',
                                                    'query' => [
                                                        'exists' => [
                                                            'field' => 'values.color-option.<all_channels>.<all_locales>',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'term' => [
                                            'values.size-option.<all_channels>.<all_locales>' => 's',
                                        ],
                                    ],
                                ],
                            ],
                        ],

                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts(
            $productsFound,
            [
                'tshirt-blue-s',
                'tshirt-red-s',
                'tshirt-unique-color-s',
                'running-shoes-s-white',
                'running-shoes-s-blue',
                'running-shoes-s-red',
                'biker-jacket-leather-s',
                'biker-jacket-polyester-s',
            ]
        );
    }

    public function testSearchNotColorGreyAndNotSizeS()
    {
        $query = [
            'query' => [
                'bool' => [
                    'should' => [

                        // Color level product & Size level parent 1
                        [
                            'bool' => [
                                'must_not' => [
                                    [
                                        'term' => [
                                            'values.color-option.<all_channels>.<all_locales>' => 'grey',
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'term' => [
                                                    'values.size-option.<all_channels>.<all_locales>' => 's',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'filter'   => [
                                    [
                                        'exists' => [
                                            'field' => 'values.color-option.<all_channels>.<all_locales>',
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'exists' => [
                                                    'field' => 'values.size-option.<all_channels>.<all_locales>',
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            'family.code' => ['tshirt-unique-size', 'shoe']
                                        ]
                                    ]
                                ],
                            ],
                        ],

                        // Size level product & Color level parent 1
                        [
                            'bool' => [
                                'must_not' => [
                                    [
                                        'term' => [
                                            'values.size-option.<all_channels>.<all_locales>' => 's',
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'term' => [
                                                    'values.color-option.<all_channels>.<all_locales>' => 'grey',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'filter'   => [
                                    [
                                        'exists' => [
                                            'field' => 'values.size-option.<all_channels>.<all_locales>',
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'exists' => [
                                                    'field' => 'values.color-option.<all_channels>.<all_locales>',
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            'family.code' => ['tshirt', 'tshirt-unique-color', 'hat']
                                        ]
                                    ]
                                ],
                            ],
                        ],

                        // Size product level & color model parent 2
                        [
                            'bool' => [
                                'must_not' => [
                                    [
                                        'term' => [
                                            'values.size-option.<all_channels>.<all_locales>' => 's',
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'has_parent' => [
                                                    'type'  => 'pim_catalog_product_model_parent_2',
                                                    'query' => [
                                                        'term' => [
                                                            'values.color-option.<all_channels>.<all_locales>' => 'grey',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                'filter'   => [
                                    [
                                        'exists' => [
                                            'field' => 'values.size-option.<all_channels>.<all_locales>',
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'has_parent' => [
                                                    'type'  => 'pim_catalog_product_model_parent_2',
                                                    'query' => [
                                                        'exists' => [
                                                            'field' => 'values.color-option.<all_channels>.<all_locales>',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'terms' => [
                                            'family.code' => ['jacket']
                                        ]
                                    ]
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts(
            $productsFound,
            [
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',

                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',

                'tshirt-unique-color-m',
                'tshirt-unique-color-l',
                'tshirt-unique-color-xl',

                'tshirt-unique-size-blue',
                'tshirt-unique-size-red',
                'tshirt-unique-size-yellow',

                'running-shoes-m-white',
                'running-shoes-m-blue',
                'running-shoes-m-red',
                'running-shoes-l-white',
                'running-shoes-l-blue',
                'running-shoes-l-red',

                'biker-jacket-leather-m',
                'biker-jacket-leather-l',
                'biker-jacket-polyester-m',
                'biker-jacket-polyester-l',
            ]
        );
    }

    public function testSearchMaterialCottonAndColorRed()
    {
        $query = [
            'query' => [
                'bool' => [
                    'should' => [
                        // Color level product & material level parent 1
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            'values.color-option.<all_channels>.<all_locales>' => 'red',
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'term' => [
                                                    'values.material-option.<all_channels>.<all_locales>' => 'cotton',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // Color level product & material level parent 2
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            'values.color-option.<all_channels>.<all_locales>' => 'red',
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'has_parent' => [
                                                    'type'  => 'pim_catalog_product_model_parent_2',
                                                    'query' => [
                                                        'term' => [
                                                            'values.material-option.<all_channels>.<all_locales>' => 'cotton',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // Color parent 2 & material level parent 1
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'has_parent' => [
                                                    'type'  => 'pim_catalog_product_model_parent_2',
                                                    'query' => [
                                                        'term' => [
                                                            'values.color-option.<all_channels>.<all_locales>' => 'red',
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'term' => [
                                                    'values.material-option.<all_channels>.<all_locales>' => 'cotton',
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],

                        // Material and color at the same level product
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'term' => [
                                            'values.color-option.<all_channels>.<all_locales>' => 'red',
                                        ],
                                    ],
                                    [
                                        'term' => [
                                            'values.material-option.<all_channels>.<all_locales>' => 'cotton',
                                        ],
                                    ],
                                ],
                            ],
                        ],

                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2',
            ]
        );

        $this->assertProducts(
            $productsFound,
            [
                'model-tshirt-red',
                'model-tshirt-unique-color',
                'tshirt-unique-size-red',
            ]
        );
    }
}
