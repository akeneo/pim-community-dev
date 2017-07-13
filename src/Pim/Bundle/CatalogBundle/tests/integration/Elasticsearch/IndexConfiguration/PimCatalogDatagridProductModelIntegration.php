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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1', AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2']
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
}
