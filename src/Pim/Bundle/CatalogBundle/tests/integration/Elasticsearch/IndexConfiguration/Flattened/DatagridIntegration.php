<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration\Flattened;

/**
 * Search use cases of products and models in a "smart datagrid way".
 * It returns either products or models depending on where is information is stored.
 *
 * Search among n attributes:
 *      - if at east one of the attributes is located at the product level for all family variants
 *          => need to search only at the product level
 *      - if all attributes are located at the root model level
 *          => need to search only at the root model level
 *
 * We should not forget to look for products that don't have a family variant.
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
            // description is only in common attributes, so it's easy
            // we just have to look for the value into 1 single index
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
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
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
        // depending on the family variant, COLOR is not at the same the level
        //  - it can be at the root model level
        //  - it can be at the sub model level
        //  - it can be at product level
        //  - it can be at product without being a variant product (aka, simple product)

        // no need to define the family variant for the root model level
        // but we need to add it for each sub level :(

        $query = [
            'query' => [
                'bool' => [
                    'should' => [
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                                    ],
                                    [
                                        'term' => ['type' => 'PimCatalogRootProductModel'],
                                    ]
                                ],
                            ],
                        ],
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                                    ],
                                    [
                                        'term' => ['type' => 'PimCatalogSubProductModel'],
                                    ],
                                    [
                                        'terms' => ['family_variant' => ['clothing_color_size']],
                                    ]
                                ],
                            ],
                        ],
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                                    ],
                                    [
                                        'term' => ['type' => 'PimCatalogProduct'],
                                    ],
                                    [
                                        'terms' => ['family_variant' => ['shoes_size_color', 'clothing_color']],
                                    ]
                                ],
                            ],
                        ],
                        [
                            'bool' => [
                                'filter'   => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                                    ],
                                    [
                                        'term' => ['type' => 'PimCatalogProduct'],
                                    ],
                                ],
                                'must_not' => [
                                    [
                                        'exists' => ['field' => 'family_variant']
                                    ],
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
                AbstractPimCatalogProductModelIntegration::DOCUMENT_TYPE,
            ]
        );

        $this->assertProducts(
            $productsFound,
            [
                'model-tshirt-red',
                'model-tshirt-unique-color',
                'tshirt-unique-size-red',
                'running-shoes-s-red',
                'running-shoes-m-red',
                'running-shoes-l-red',
            ]
        );
    }

//    public function testSearchColorGrey()
//    {
//        $query = [
//            'query' => [
//                'bool' => [
//                    'filter' => [
//                        'terms' => [
//                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
//                        ],
//                    ],
//                ],
//            ],
//        ];
//
//        $productsFound = $this->getSearchQueryResults(
//            $query,
//            [
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
//            ]
//        );
//
//        $this->assertProducts($productsFound, ['model-tshirt-grey', 'model-hat']);
//    }
//
//    public function testSearchColorBlue()
//    {
//        $query = [
//            'query' => [
//                'bool' => [
//                    'filter' => [
//                        'terms' => [
//                            'values.color-option.<all_channels>.<all_locales>' => ['blue'],
//                        ],
//                    ],
//                ],
//            ],
//        ];
//
//        $productsFound = $this->getSearchQueryResults(
//            $query,
//            [
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
//            ]
//        );
//
//        $this->assertProducts(
//            $productsFound,
//            [
//                'model-tshirt-blue',
//                'tshirt-unique-size-blue',
//                'running-shoes-s-blue',
//                'running-shoes-m-blue',
//                'running-shoes-l-blue',
//                'watch',
//            ]
//        );
//    }
//
//    public function testSearchSizeS()
//    {
//        $query = [
//            'query' => [
//                'bool' => [
//                    'filter' => [
//                        'terms' => [
//                            'values.size-option.<all_channels>.<all_locales>' => ['s'],
//                        ],
//                    ],
//                ],
//            ],
//        ];
//
//        $productsFound = $this->getSearchQueryResults(
//            $query,
//            [
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
//            ]
//        );
//
//        $this->assertProducts(
//            $productsFound,
//            [
//                'tshirt-grey-s',
//                'tshirt-blue-s',
//                'tshirt-red-s',
//                'tshirt-unique-color-s',
//                'model-running-shoes-s',
//                'biker-jacket-leather-s',
//                'biker-jacket-polyester-s',
//            ]
//        );
//    }
//
//    public function testSearchSizeM()
//    {
//        $query = [
//            'query' => [
//                'bool' => [
//                    'filter' => [
//                        'terms' => [
//                            'values.size-option.<all_channels>.<all_locales>' => ['m'],
//                        ],
//                    ],
//                ],
//            ],
//        ];
//
//        $productsFound = $this->getSearchQueryResults(
//            $query,
//            [
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
//            ]
//        );
//
//        $this->assertProducts(
//            $productsFound,
//            [
//                'tshirt-grey-m',
//                'tshirt-red-m',
//                'tshirt-blue-m',
//                'tshirt-unique-color-m',
//                'hat-m',
//                'model-running-shoes-m',
//                'biker-jacket-leather-m',
//                'biker-jacket-polyester-m',
//            ]
//        );
//    }

    public function testSearchColorGreyAndSizeS()
    {
        // depending on the family variant, COLOR and SIZE are not at the same the level
        // but in that particular case, there is always one attribute among COLOR or SIZE, that is present
        // on the product level

        // which means we only have to filter on the product level

        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                        ],
                        [
                            'term' => ['type' => 'PimCatalogProduct'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::DOCUMENT_TYPE,
            ]
        );

        $this->assertProducts($productsFound, ['tshirt-grey-s']);
    }

//    public function testSearchColorGreyAndSizeM()
//    {
//        $query = [
//            'query' => [
//                'bool' => [
//                    'minimum_should_match' => 1,
//                    'should'               => [
//                        [
//                            'bool' => [
//                                'filter' => [
//                                    [
//                                        'has_parent' => [
//                                            'type'  => 'pim_catalog_product_model_parent_root',
//                                            'query' => [
//                                                'terms' => [
//                                                    'values.color-option.<all_channels>.<all_locales>' => ['grey'],
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                    [
//                                        'terms' => [
//                                            'values.size-option.<all_channels>.<all_locales>' => ['m'],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//                        [
//                            'bool' => [
//                                'filter' => [
//                                    [
//                                        'has_parent' => [
//                                            'type'  => 'pim_catalog_product_model_parent_root',
//                                            'query' => [
//                                                'terms' => [
//                                                    'values.size-option.<all_channels>.<all_locales>' => ['m'],
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                    [
//                                        'terms' => [
//                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//                        [
//                            'bool' => [
//                                'filter' => [
//                                    [
//                                        'has_parent' => [
//                                            'type'  => 'pim_catalog_product_model_parent_root',
//                                            'query' => [
//                                                'has_parent' => [
//                                                    'type'  => 'pim_catalog_product_model_parent_sub',
//                                                    'query' => [
//                                                        'terms' => [
//                                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
//                                                        ],
//                                                    ],
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                    [
//                                        'terms' => [
//                                            'values.size-option.<all_channels>.<all_locales>' => ['m'],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//                    ],
//                ],
//            ],
//        ];
//
//        $productsFound = $this->getSearchQueryResults(
//            $query,
//            [
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
//            ]
//        );
//
//        $this->assertProducts($productsFound, ['tshirt-grey-m', 'hat-m']);
//    }
//
//    /**
//     * Search for a model parent 1 in its values and the value of his parent.
//     *
//     * @group todo
//     */
//    public function testSearchColorGreyAndDescriptionTshirt()
//    {
//        $query = [
//            'query' => [
//                'bool' => [
//                    'minimum_should_match' => 1,
//                    'should'               => [
//                        // Color in level 1 - Description in level 2
//                        [
//                            'bool' => [
//                                'filter' => [
//                                    [
//                                        'has_parent' => [
//                                            'type'  => 'pim_catalog_product_model_parent_sub',
//                                            'query' => [
//                                                'query_string' => [
//                                                    'default_field' => 'values.description-text.<all_channels>.<all_locales>',
//                                                    'query'         => '*T-shirt*',
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                    [
//                                        'terms' => [
//                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//
//                        // Color and description in level 1
//                        [
//                            'bool' => [
//                                'filter' => [
//                                    [
//                                        'has_parent' => [
//                                            'type'  => 'pim_catalog_product_model_parent_root',
//                                            'query' => [
//                                                'query_string' => [
//                                                    'default_field' => 'values.description-text.<all_channels>.<all_locales>',
//                                                    'query'         => '*T-shirt*',
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                    [
//                                        'has_parent' => [
//                                            'type'  => 'pim_catalog_product_model_parent_root',
//                                            'query' => [
//                                                'terms' => [
//                                                    'values.color-option.<all_channels>.<all_locales>' => ['grey'],
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//
//                        // Color and Description in level product
//                        [
//                            'bool' => [
//                                'filter' => [
//                                    [
//                                        'query_string' => [
//                                            'default_field' => 'values.description-text.<all_channels>.<all_locales>',
//                                            'query'         => '*T-shirt*',
//                                        ],
//                                    ],
//                                    [
//                                        'terms' => [
//                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//
//                        // Color in level product and description in level 1
//                        [
//                            'bool' => [
//                                'filter' => [
//                                    [
//                                        'has_parent' => [
//                                            'type'  => 'pim_catalog_product_model_parent_root',
//                                            'query' => [
//                                                'query_string' => [
//                                                    'default_field' => 'values.description-text.<all_channels>.<all_locales>',
//                                                    'query'         => '*T-shirt*',
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                    [
//                                        'terms' => [
//                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//
//                        // Color in level product and description in level 2
//                        [
//                            'bool' => [
//                                'filter' => [
//                                    [
//                                        'has_parent' => [
//                                            'type'  => 'pim_catalog_product_model_parent_root',
//                                            'query' => [
//                                                'has_parent' => [
//                                                    'type'  => 'pim_catalog_product_model_parent_sub',
//                                                    'query' => [
//                                                        'query_string' => [
//                                                            'default_field' => 'values.description-text.<all_channels>.<all_locales>',
//                                                            'query'         => '*T-shirt*',
//                                                        ],
//
//                                                    ],
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                    [
//                                        'terms' => [
//                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//
//                        // Color and description in level 2
//                        [
//                            'bool' => [
//                                'filter' => [
//                                    [
//                                        'has_parent' => [
//                                            'type'  => 'pim_catalog_product_model_parent_root',
//                                            'query' => [
//                                                'has_parent' => [
//                                                    'type'  => 'pim_catalog_product_model_parent_sub',
//                                                    'query' => [
//                                                        'query_string' => [
//                                                            'default_field' => 'values.description-text.<all_channels>.<all_locales>',
//                                                            'query'         => '*T-shirt*',
//                                                        ],
//
//                                                    ],
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                    [
//                                        'has_parent' => [
//                                            'type'  => 'pim_catalog_product_model_parent_root',
//                                            'query' => [
//                                                'has_parent' => [
//                                                    'type'  => 'pim_catalog_product_model_parent_sub',
//                                                    'query' => [
//                                                        'terms' => [
//                                                            'values.color-option.<all_channels>.<all_locales>' => ['grey'],
//                                                        ],
//                                                    ],
//                                                ],
//                                            ],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//                    ],
//                ],
//            ],
//        ];
//
//        $productsFound = $this->getSearchQueryResults(
//            $query,
//            [
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
//            ]
//        );
//
//        $this->assertProducts($productsFound, ['model-tshirt-grey']);
//    }
//
//    public function testSearchMaterialCotton()
//    {
//        $query = [
//            'query' => [
//                'bool' => [
//                    'filter' => [
//                        'terms' => [
//                            'values.material-option.<all_channels>.<all_locales>' => ['cotton'],
//                        ],
//                    ],
//                ],
//            ],
//        ];
//
//        $productsFound = $this->getSearchQueryResults(
//            $query,
//            [
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
//            ]
//        );
//
//        $this->assertProducts(
//            $productsFound,
//            [
//                'model-tshirt-grey',
//                'model-tshirt-red',
//                'model-tshirt-unique-color',
//                'model-tshirt-unique-size',
//            ]
//        );
//    }
//
//    public function testSearchMaterialLeather()
//    {
//        $query = [
//            'query' => [
//                'bool' => [
//                    'filter' => [
//                        'terms' => [
//                            'values.material-option.<all_channels>.<all_locales>' => ['leather'],
//                        ],
//                    ],
//                ],
//            ],
//        ];
//
//        $productsFound = $this->getSearchQueryResults(
//            $query,
//            [
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
//            ]
//        );
//
//        $this->assertProducts(
//            $productsFound,
//            [
//                'model-running-shoes',
//                'model-biker-jacket-leather',
//            ]
//        );
//    }
//
//    /**
//     * Is not part of any use case but a proof of concept regarding the query of an attribute which is hold by the
//     * model_parent_sub (grand father).
//     */
//    public function testSearchSizeMAndGrandParentColorWhite()
//    {
//        $query = [
//            'query' => [
//                'bool' => [
//                    'filter' => [
//                        [
//                            'has_parent' => [
//                                'type'  => 'pim_catalog_product_model_parent_root',
//                                'query' => [
//                                    'has_parent' => [
//                                        'type'  => 'pim_catalog_product_model_parent_sub',
//                                        'query' => [
//                                            'terms' => [
//                                                'values.color-option.<all_channels>.<all_locales>' => ['white'],
//                                            ],
//                                        ],
//                                    ],
//                                ],
//                            ],
//                        ],
//                        [
//                            'terms' => [
//                                'values.size-option.<all_channels>.<all_locales>' => ['m'],
//                            ],
//                        ],
//                    ],
//                ],
//            ],
//        ];
//
//        $productsFound = $this->getSearchQueryResults(
//            $query,
//            [
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
//                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
//            ]
//        );
//
//        $this->assertProducts($productsFound, ['biker-jacket-polyester-m', 'biker-jacket-leather-m']);
//    }

    public function testNotGrey()
    {
        // depending on the family variant, COLOR is not at the same the level
        //  - it can be at the root model level
        //  - it can be at the sub model level
        //  - it can be at product level
        //  - it can be at product without being a variant product (aka, simple product)

        // no need to define the family variant for the root model level
        // but we need to add it for each sub level :(

        $query = [
            'query' => [
                'bool' => [
                    'should' => [
                        [
                            'bool' => [
                                'must_not' => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                                    ],
                                ],
                                'filter'   => [
                                    [
                                        'term' => ['type' => 'PimCatalogRootProductModel'],
                                    ],
                                    [
                                        'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
                                    ],
                                ]
                            ],
                        ],
                        [
                            'bool' => [
                                'must_not' => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                                    ],
                                ],
                                'filter'   => [
                                    [
                                        'term' => ['type' => 'PimCatalogSubProductModel'],
                                    ],
                                    [
                                        'terms' => ['family_variant' => ['clothing_color_size']],
                                    ],
                                    [
                                        'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'bool' => [
                                'must_not' => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                                    ],
                                ],
                                'filter'   => [
                                    [
                                        'term' => ['type' => 'PimCatalogProduct'],
                                    ],
                                    [
                                        'terms' => ['family_variant' => ['shoes_size_color', 'clothing_color']],
                                    ],
                                    [
                                        'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'bool' => [
                                'must_not' => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                                    ],
                                    [
                                        'exists' => ['field' => 'family_variant']
                                    ],
                                ],
                                'filter'   => [
                                    [
                                        'term' => ['type' => 'PimCatalogProduct'],
                                    ],
                                    [
                                        'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
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
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
                AbstractPimCatalogProductModelIntegration::DOCUMENT_TYPE,
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

    public function testRedCotton()
    {
        // depending on the family variant, COLOR and MATERIAL are not at the same the level
        //  - it can be at the root model level
        //  - it can be at the sub model level
        //  - it can be at product level
        //  - it can be at product without being a variant product (aka, simple product)

        // no need to define the family variant for the root model level
        // but we need to add it for each sub level :(

        $query = [
            'query' => [
                'bool' => [
                    'should' => [
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                                    ],
                                    [
                                        'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['cotton']],
                                    ],
                                    [
                                        'term' => ['type' => 'PimCatalogRootProductModel'],
                                    ],
                                ]
                            ],
                        ],
                        [
                            'bool' => [

                                'filter' => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                                    ],
                                    [
                                        'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['cotton']],
                                    ],
                                    [
                                        'term' => ['type' => 'PimCatalogSubProductModel'],
                                    ],
                                    [
                                        'terms' => [
                                            'family_variant' => [
                                                'clothing_color_size',
                                                'clothing_material_size'
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'bool' => [
                                'filter' => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                                    ],
                                    [
                                        'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['cotton']],
                                    ],
                                    [
                                        'term' => ['type' => 'PimCatalogProduct'],
                                    ],
                                    [
                                        'terms' => ['family_variant' => ['clothing_color', 'shoes_size_color']],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'bool' => [
                                'filter'   => [
                                    [
                                        'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                                    ],
                                    [
                                        'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['cotton']],
                                    ],
                                    [
                                        'term' => ['type' => 'PimCatalogProduct'],
                                    ],
                                ],
                                'must_not' => [
                                    [
                                        'exists' => ['field' => 'family_variant']
                                    ],
                                ]
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_root',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_sub',
                AbstractPimCatalogProductModelIntegration::DOCUMENT_TYPE,
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
