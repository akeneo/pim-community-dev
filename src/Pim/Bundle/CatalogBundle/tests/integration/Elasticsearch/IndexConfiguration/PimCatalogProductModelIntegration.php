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
        $productModels = [
            // simple tshirt - level 0
            [
                'identifier' => 'model-tshirt-level-0',
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
                'parent_id'  => 'model-tshirt-level-0',
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
                'parent_id'  => 'model-tshirt-level-0',
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
                'parent_id'  => 'model-tshirt-level-0',
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
                'parent_id'  => 'model-tshirt-level-1-grey',
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
                'parent_id'  => 'model-tshirt-level-1-grey',
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
                'parent_id'  => 'model-tshirt-level-1-grey',
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
                'parent_id'  => 'model-tshirt-level-1-grey',
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
                'parent_id'  => 'model-tshirt-level-1-blue',
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
                'parent_id'  => 'model-tshirt-level-1-blue',
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
                'parent_id'  => 'model-tshirt-level-1-blue',
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
                'parent_id'  => 'model-tshirt-level-1-blue',
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
                'parent_id'  => 'model-tshirt-level-1-red',
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
                'parent_id'  => 'model-tshirt-level-1-red',
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
                'parent_id'  => 'model-tshirt-level-1-red',
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
                'parent_id'  => 'model-tshirt-level-1-red',
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
                'parent_id'  => 'model-tshirt-unique-level-0',
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
                'parent_id'  => 'model-tshirt-unique-level-0',
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
                'parent_id'  => 'model-tshirt-unique-level-0',
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
                'parent_id'  => 'model-tshirt-unique-level-0',
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
                ],
            ],

            // Hats variants (varying on size)
            [
                'identifier' => 'hat-m',
                'parent_id'  => 'model-hat-level-0',
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
                'parent_id'  => 'model-hat-level-0',
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

        $this->indexProducts($productModels);
        $this->indexProducts($productVariants);
    }
}
