<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimCatalogProductModelIntegration extends AbstractPimCatalogIntegration
{
    public function testSearchTshirt()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'values.description-text.<all_channels>.<all_locales>',
                            'query'         => '*T-shirt*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertProducts($productsFound, ['t-shirt-product-model-level-x', 't-shirt-unique-level-x']);
    }
    /**
     * {@inheritdoc}
     */
    protected function addProducts()
    {
        $products = [
            // Tshirt: color-size
            [
                'identifier' => 'tshirt-white-size-s',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-white-size-m',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-white-size-l',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-white-size-xl',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-red-size-s',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-red-size-m',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-red-size-l',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-red-size-xl',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-blue-size-s',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-blue-size-m',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-blue-size-l',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-blue-size-xl',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 't-shirt with a round neck Divided'
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            // T-shirt: size
            [
                'identifier' => 'tshirt-uniq-color-size-s',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-uniq-color-size-m',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-uniq-color-size-l',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'tshirt-uniq-color-size-xl',
                'family'     => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des t-shirts',
                    ],
                ],
                'values'     => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif',
                        ],
                    ],
                    'size-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            // Watch
            [
                'identifier' => 'watch',
                'family'     => [
                    'code'   => 'watch',
                    'labels' => [
                        'fr_FR' => 'La montre unique',
                    ],

                ],
                'values' => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Metal watch blue/white striped',
                        ]
                    ]
                ]
            ],

            // Hats: size
            [
                'identifier' => 'hat-m',
                'family'     => [
                    'code'   => 'hats',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'values' => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Braided hat',
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'grey'
                        ]
                    ],
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm'
                        ]
                    ]
                ]
            ],
            [
                'identifier' => 'hat-l',
                'family'     => [
                    'code'   => 'hats',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'values' => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Braided hat',
                        ]
                    ],
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'grey'
                        ]
                    ],
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l'
                        ]
                    ]
                ]
            ],
        ];

        $this->indexProducts($products);
    }
}
