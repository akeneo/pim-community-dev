<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration\FlattenedAndAttributeList;

/**
 * Search use cases of products and models in a "smart datagrid way".
 * It returns either products or models depending on where is information is stored.
 *
 * The search is performed independently from the family variants.
 *
 * The search takes advantage of the following properties to elaborate concise but powerful requests:
 * - Each document (e.g: products, product variants or models) has all the properties of it's associated parent model
 *   and grand parent models.
 * - Each documents has an 'owned_attributes' property which is a list of the attribute codes that belong to the
 *   document (following the family variant settings and levels definition).
 * TODO: Rename property type to 'product_type' ? Does the real products need to have this type ? In the dataset the
 *       products and product variants does not have the same property (for the default display of the datagrid). is tha
 *       wise ?
 * - Each document has a property 'type' which gives an hint about the level of the family variant the document
 *   belongs to.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimCatalogDatagridProductModelIntegration extends AbstractPimCatalogProductModelIntegration
{
    /**
     * Default display is: search for the root product models and products".
     */
    public function testDefaultDisplay()
    {
        $query = [
            'query' => [
                'bool' => [
                   'filter' => [
                       'terms' => [
                           'type' => ['PimCatalogRootProductModel', 'PimCatalogProduct'],
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

        $this->assertProducts(
            $productsFound,
            [
                'model-tshirt',
                'model-tshirt-unique-color',
                'watch',
                'model-hat',
                'model-tshirt-unique-size',
                'model-running-shoes',
                'model-biker-jacket',
            ]
        );
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
                        [
                            'terms' => ['owned_attributes' => ['description']],
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
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['color']],
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

    public function testSearchColorGrey()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['color']],
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
        $this->assertProducts($productsFound, ['model-tshirt-grey', 'model-hat']);
    }

    public function testSearchColorBlue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['blue']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['color']],
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

        $this->assertProducts(
            $productsFound,
            [
                'model-tshirt-blue',
                'tshirt-unique-size-blue',
                'running-shoes-s-blue',
                'running-shoes-m-blue',
                'running-shoes-l-blue',
                'watch',
            ]
        );
    }

    public function testSearchSizeS()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['size']],
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
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['m']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['size']],
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
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['color', 'size']],
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

    public function testSearchColorGreyAndSizeM()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['m']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['color', 'size']],
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

        $this->assertProducts($productsFound, ['tshirt-grey-m', 'hat-m']);
    }

    public function testSearchColorGreyAndDescriptionTshirt()
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
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['color', 'description']],
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

        $this->assertProducts($productsFound, ['model-tshirt-grey']);
    }

    public function testSearchMaterialCotton()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['cotton']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['material']],
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
                        [
                            'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['leather']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['material']],
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
        $this->assertProducts(
            $productsFound,
            [
                'model-running-shoes',
                'model-biker-jacket-leather',
            ]
        );
    }

    public function testSearchSizeMAndGrandParentColorWhite()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['m']],
                        ],
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['white']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['size', 'color']],
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

        $this->assertProducts($productsFound,
            ['running-shoes-m-white', 'biker-jacket-polyester-m', 'biker-jacket-leather-m']);
    }

    public function testNotGrey()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                    ],
                    'filter'   => [
                        [
                            'terms' => ['owned_attributes' => ['color']],
                        ],
                        [
                            'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
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
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['cotton']],
                        ],
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['color', 'material']],
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

        $this->assertProducts(
            $productsFound,
            [
                'model-tshirt-red',
                'model-tshirt-unique-color',
                'tshirt-unique-size-red',
            ]
        );
    }

    public function testNotGreyAndS()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                    ],
                    'filter'   => [
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                        ],
                        [
                            'terms' => ['owned_attributes' => ['color', 'size']],
                        ],
                        [
                            'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
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

    public function testNotGreyAndNotS()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                        ],
                    ],
                    'filter'   => [
                        [
                            'terms' => ['owned_attributes' => ['color', 'size']],
                        ],
                        [
                            'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
                        ],
                        [
                            'exists' => ['field' => 'values.size-option.<all_channels>.<all_locales>'],
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
}
