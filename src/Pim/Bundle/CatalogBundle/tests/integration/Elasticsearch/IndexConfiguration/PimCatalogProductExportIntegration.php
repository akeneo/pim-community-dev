<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * Search use cases of products for the "export builder way".
 * It returns products no matter which information is looked for.
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimCatalogProductExportIntegration extends AbstractPimCatalogProductModelIntegration
{
    public function testSearchColorRed()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'term' => [
                                'type' => 'PimCatalogProduct'
                            ],
                        ],
                        [
                            'bool' => [
                                'should' => [
                                    [
                                        'terms' => [
                                            'values.color-option.<all_channels>.<all_locales>' => ['red'],
                                        ],
                                    ],
                                    [
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'terms' => [
                                                    'values.color-option.<all_channels>.<all_locales>' => ['red'],
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
                                                            'values.color-option.<all_channels>.<all_locales>' => ['red'],
                                                        ],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ]
                        ]
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2'
            ]
        );

        $this->assertProducts(
            $productsFound,
            [
                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',
                'tshirt-unique-color-s',
                'tshirt-unique-color-m',
                'tshirt-unique-color-l',
                'tshirt-unique-color-xl',
                'tshirt-unique-size-red',
                'running-shoes-s-red',
                'running-shoes-m-red',
                'running-shoes-l-red',
            ]
        );
    }

    /**
     * Dirty version.
     *
     * Number of SHOULD clauses = number of levels ^ number of filters
     *
     * The SHOULD clause contains all combinations possible of color Red, size S, aka:
     *      - product color red AND product size S
     *      - product color red AND model 1 size S
     *      - product color red AND model 2 size S
     *      - model 1 color red AND product size S
     *      - model 1 color red AND model 1 size S
     *      - model 1 color red AND model 2 size S
     *      - model 2 color red AND product size S
     *      - model 2 color red AND model 1 size S
     *      - model 2 color red AND model 2 size S
     */
    public function testSearchColorRedSizeSdirty()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'term' => [
                                'type' => 'PimCatalogProduct'
                            ],
                        ],
                        [
                            'bool' => [
                                'should' => [
                                    [
                                        // product color red AND product size S
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'terms' => [
                                                        'values.color-option.<all_channels>.<all_locales>' => ['red'],
                                                    ],
                                                ],
                                                [
                                                    'terms' => [
                                                        'values.size-option.<all_channels>.<all_locales>' => ['s'],
                                                    ],
                                                ],
                                            ]
                                        ]
                                    ],
                                    [
                                        // product color red AND model 1 size S
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'terms' => [
                                                        'values.color-option.<all_channels>.<all_locales>' => ['red'],
                                                    ],
                                                ],
                                                [
                                                    'has_parent' => [
                                                        'type'  => 'pim_catalog_product_model_parent_1',
                                                        'query' => [
                                                            'terms' => [
                                                                'values.size-option.<all_channels>.<all_locales>' => ['s'],
                                                            ],
                                                        ],
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        // product color red AND model 2 size S
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'terms' => [
                                                        'values.color-option.<all_channels>.<all_locales>' => ['red'],
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
                                                                        'values.size-option.<all_channels>.<all_locales>' => ['s'],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        // model 1 color red AND product size S
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'terms' => [
                                                        'values.size-option.<all_channels>.<all_locales>' => ['s'],
                                                    ],
                                                ],
                                                [
                                                    'has_parent' => [
                                                        'type'  => 'pim_catalog_product_model_parent_1',
                                                        'query' => [
                                                            'terms' => [
                                                                'values.color-option.<all_channels>.<all_locales>' => ['red'],
                                                            ],
                                                        ],
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        // model 1 color red AND model 1 size S
                                        'has_parent' => [
                                            'type'  => 'pim_catalog_product_model_parent_1',
                                            'query' => [
                                                'bool' => [
                                                    'must' => [
                                                        [
                                                            'terms' => [
                                                                'values.size-option.<all_channels>.<all_locales>' => ['s'],
                                                            ],
                                                        ],
                                                        [
                                                            'terms' => [
                                                                'values.color-option.<all_channels>.<all_locales>' => ['red'],
                                                            ],
                                                        ]
                                                    ]
                                                ]
                                            ],
                                        ],
                                    ],
                                    [
                                        // model 1 color red AND model 2 size S
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'has_parent' => [
                                                        'type'  => 'pim_catalog_product_model_parent_1',
                                                        'query' => [
                                                            'terms' => [
                                                                'values.color-option.<all_channels>.<all_locales>' => ['red'],
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
                                                                        'values.size-option.<all_channels>.<all_locales>' => ['s'],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        // model 2 color red AND product size S
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'terms' => [
                                                        'values.size-option.<all_channels>.<all_locales>' => ['s'],
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
                                                                        'values.color-option.<all_channels>.<all_locales>' => ['red'],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        // model 2 color red AND model 1 size S
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'has_parent' => [
                                                        'type'  => 'pim_catalog_product_model_parent_1',
                                                        'query' => [
                                                            'has_parent' => [
                                                                'type'  => 'pim_catalog_product_model_parent_2',
                                                                'query' => [
                                                                    'terms' => [
                                                                        'values.color-option.<all_channels>.<all_locales>' => ['red'],
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
                                                            'terms' => [
                                                                'values.size-option.<all_channels>.<all_locales>' => ['s'],
                                                            ],
                                                        ],
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        // model 2 color red AND model 2 size S
                                        'bool' => [
                                            'must' => [
                                                [
                                                    'has_parent' => [
                                                        'type'  => 'pim_catalog_product_model_parent_1',
                                                        'query' => [
                                                            'has_parent' => [
                                                                'type'  => 'pim_catalog_product_model_parent_2',
                                                                'query' => [
                                                                    'terms' => [
                                                                        'values.color-option.<all_channels>.<all_locales>' => ['red'],
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
                                                                        'values.size-option.<all_channels>.<all_locales>' => ['s'],
                                                                    ],
                                                                ],
                                                            ],
                                                        ],
                                                    ],
                                                ]
                                            ]
                                        ]
                                    ],
                                ],
                            ]
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_1',
                AbstractPimCatalogProductModelIntegration::PRODUCT_MODEL_DOCUMENT_TYPE . '_2'
            ]
        );

        $this->assertProducts(
            $productsFound,
            [
                'tshirt-red-s',
                'tshirt-unique-color-s',
                'running-shoes-s-red',
            ]
        );
    }
}
