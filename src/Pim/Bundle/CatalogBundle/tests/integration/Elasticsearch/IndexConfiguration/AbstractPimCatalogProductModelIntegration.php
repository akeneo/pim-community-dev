<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractPimCatalogProductModelIntegration extends AbstractPimCatalogIntegration
{
    const PRODUCT_MODEL_DOCUMENT_TYPE = 'pim_catalog_product_model_parent';

    /**
     * {@inheritdoc}
     */
    protected function addProducts()
    {
        $productModels = [
            // simple tshirt
            [
                'identifier'  => 'model-tshirt',
                'type' => 'PimCatalogProductModel',
                'level'       => 2,
                'family'      => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'      => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                ],
            ],

            // Tshirt model level-1 (varying on color)
            [
                'identifier'    => 'model-tshirt-grey',
                'type'   => 'PimCatalogProductModel',
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
                    'image-media' => [
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
                'type'   => 'PimCatalogProductModel',
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
                    'image-media' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-blue.jpg',
                        ],
                    ],
                    'material-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'model-tshirt-red',
                'type'   => 'PimCatalogProductModel',
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
                    'image-media' => [
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
                'type'   => 'PimCatalogProductModel',
                'parent'        => 'model-tshirt-unique-color',
                'root_ancestor' => 'model-tshirt-unique-color',
                'level'         => 1,
                'family'        => [
                    'code'   => 'tshirt-unique-color',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
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
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'cotton',
                        ],
                    ],
                ],
            ],

            // Hats model
            [
                'identifier'    => 'model-hat',
                'type'   => 'PimCatalogProductModel',
                'parent'        => 'model-hat',
                'root_ancestor' => 'model-hat',
                'level'         => 1,
                'family'        => [
                    'code'   => 'hat',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'values'        => [
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
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'wool',
                        ],
                    ],
                ],
            ],

            // Tshirt unique size model
            [
                'identifier'    => 'model-tshirt-unique-size',
                'type'   => 'PimCatalogProductModel',
                'parent'        => 'model-tshirt-unique-size',
                'root_ancestor' => 'model-tshirt-unique-size',
                'level'         => 1,
                'family'        => [
                    'code'   => 'tshirt-unique-size',
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
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-unique-size.jpg',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'u',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'cotton',
                        ],
                    ],
                ],
            ],

            // Running shoes
            [
                'identifier'  => 'model-running-shoes',
                'type' => 'PimCatalogProductModel',
                'level'       => 2,
                'family'      => [
                    'code'   => 'shoe',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'      => [
                    'material-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'model-running-shoes-s',
                'type'   => 'PimCatalogProductModel',
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
                'type'   => 'PimCatalogProductModel',
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
                'type'   => 'PimCatalogProductModel',
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
                'identifier'  => 'model-biker-jacket',
                'type' => 'PimCatalogProductModel',
                'level'       => 2,
                'family'      => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'      => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'model-biker-jacket-leather',
                'type'   => 'PimCatalogProductModel',
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
                'type'   => 'PimCatalogProductModel',
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
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-grey',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
                'identifier'    => 'tshirt-grey-m',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-grey',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
                'identifier'    => 'tshirt-grey-l',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-grey',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
            [
                'identifier'    => 'tshirt-grey-xl',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-grey',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'tshirt-blue-s',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-blue',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
                'identifier'    => 'tshirt-blue-m',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-blue',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
                'identifier'    => 'tshirt-blue-l',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-blue',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
            [
                'identifier'    => 'tshirt-blue-xl',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-blue',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'tshirt-red-s',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-red',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
                'identifier'    => 'tshirt-red-m',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-red',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
                'identifier'    => 'tshirt-red-l',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-red',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
            [
                'identifier'    => 'tshirt-red-xl',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-red',
                'root_ancestor' => 'model-tshirt',
                'family'        => [
                    'code'   => 'tshirt',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            // T-shirt: size
            [
                'identifier'    => 'tshirt-unique-color-s',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-color',
                'root_ancestor' => 'model-tshirt-unique-color',
                'family'        => [
                    'code'   => 'tshirt-unique-color',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
                'identifier'    => 'tshirt-unique-color-m',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-color',
                'root_ancestor' => 'model-tshirt-unique-color',
                'family'        => [
                    'code'   => 'tshirt-unique-color',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
                'identifier'    => 'tshirt-unique-color-l',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-color',
                'root_ancestor' => 'model-tshirt-unique-color',
                'family'        => [
                    'code'   => 'tshirt-unique-color',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
            [
                'identifier'    => 'tshirt-unique-color-xl',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-color',
                'root_ancestor' => 'model-tshirt-unique-color',
                'family'        => [
                    'code'   => 'tshirt-unique-color',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'size-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            // Watch
            [
                'identifier'    => 'watch',
                'type'   => 'PimCatalogProduct',
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
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-hat',
                'root_ancestor' => 'model-hat',
                'family'        => [
                    'code'   => 'hat',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
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
                'identifier'    => 'hat-l',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-hat',
                'root_ancestor' => 'model-hat',
                'family'        => [
                    'code'   => 'hat',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
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

            // Tshirt unique size model
            [
                'identifier'    => 'tshirt-unique-size-blue',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-size',
                'root_ancestor' => 'model-tshirt-unique-size',
                'level'         => 0,
                'family'        => [
                    'code'   => 'tshirt-unique-size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
                'identifier'    => 'tshirt-unique-size-red',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-size',
                'root_ancestor' => 'model-tshirt-unique-size',
                'level'         => 0,
                'family'        => [
                    'code'   => 'tshirt-unique-size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
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
                'identifier'    => 'tshirt-unique-size-yellow',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-tshirt-unique-size',
                'root_ancestor' => 'model-tshirt-unique-size',
                'level'         => 0,
                'family'        => [
                    'code'   => 'tshirt-unique-size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'values'        => [
                    'color-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'yellow',
                        ],
                    ],
                ],
            ],

            // Running shoes
            [
                'identifier'    => 'running-shoes-s-white',
                'type'   => 'PimCatalogProduct',
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
                'type'   => 'PimCatalogProduct',
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
                'type'   => 'PimCatalogProduct',
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
                'type'   => 'PimCatalogProduct',
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
                'type'   => 'PimCatalogProduct',
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
                'type'   => 'PimCatalogProduct',
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
                'type'   => 'PimCatalogProduct',
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
                'type'   => 'PimCatalogProduct',
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
                'type'   => 'PimCatalogProduct',
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
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-leather',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'biker-jacket-leather-m',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-leather',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'biker-jacket-leather-l',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-leather',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],

            [
                'identifier'    => 'biker-jacket-polyester-s',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-polyester',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'biker-jacket-polyester-m',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-polyester',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'    => 'biker-jacket-polyester-l',
                'type'   => 'PimCatalogProduct',
                'parent'        => 'model-biker-jacket-polyester',
                'root_ancestor' => 'model-biker-jacket',
                'family'        => [
                    'code'   => 'jacket',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'values'        => [
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
