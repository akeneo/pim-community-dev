<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration\FlattenedAndAttributeList;

use Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration\AbstractPimCatalogIntegration;

/**
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractPimCatalogProductModelIntegration extends AbstractPimCatalogIntegration
{
    const PRODUCT_MODEL_DOCUMENT_TYPE = 'pim_catalog_product_model';

    /**
     * {@inheritdoc}
     */
    protected function addProducts()
    {
        $rootProductModels = [
            // simple tshirt
            [
                'identifier'     => 'model-tshirt',
                'type'           => 'PimCatalogRootProductModel',
                'level'          => 2,
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['description'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                ],
            ],

            // Tshirt unique color model
            [
                'identifier'     => 'model-tshirt-unique-color',
                'type'           => 'PimCatalogRootProductModel',
                'level'          => 1,
                'family_variant' => 'clothing_size',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['description', 'color', 'material'],
                'values'         => [
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
                'identifier'     => 'model-hat',
                'type'           => 'PimCatalogRootProductModel',
                'level'          => 1,
                'family_variant' => 'accessories_size',
                'family'         => [
                    'code'   => 'accessories',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'owned_attributes' => ['description', 'color', 'material'],
                'values'         => [
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
                'identifier'     => 'model-tshirt-unique-size',
                'type'           => 'PimCatalogRootProductModel',
                'level'          => 1,
                'family_variant' => 'clothing_color',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['description', 'size', 'material'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'u',
                        ],
                    ],
                    'material-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'cotton',
                        ],
                    ]
                ],
            ],

            // Running shoes
            [
                'identifier'     => 'model-running-shoes',
                'type'           => 'PimCatalogRootProductModel',
                'level'          => 2,
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['description', 'material'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
                        ],
                    ],
                    'material-option' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ]
                ],
            ],

            // Biker jacket
            [
                'identifier'     => 'model-biker-jacket',
                'type'           => 'PimCatalogRootProductModel',
                'level'          => 2,
                'family_variant' => 'clothing_material_size',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['description', 'color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'la jacket du biker ouaip ouaip',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                ],
            ],
        ];

        $subProductModels = [
            // Tshirt model level-1 (varying on color)
            [
                'identifier'     => 'model-tshirt-grey',
                'type'           => 'PimCatalogSubProductModel',
                'level'          => 1,
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['color', 'material'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'grey',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-grey.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'cotton',
                        ],
                    ],
                ],
            ],
            [
                'identifier'     => 'model-tshirt-blue',
                'type'           => 'PimCatalogSubProductModel',
                'level'          => 1,
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['color', 'material'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-blue.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
                        ],
                    ],
                ],
            ],
            [
                'identifier'     => 'model-tshirt-red',
                'type'           => 'PimCatalogSubProductModel',
                'level'          => 1,
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['color', 'material'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-red.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'cotton',
                        ],
                    ],
                ],
            ],
            [
                'identifier'     => 'model-running-shoes-s',
                'type'           => 'PimCatalogSubProductModel',
                'level'          => 1,
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
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
                'identifier'     => 'model-running-shoes-m',
                'type'           => 'PimCatalogSubProductModel',
                'level'          => 1,
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
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
                'identifier'     => 'model-running-shoes-l',
                'type'           => 'PimCatalogSubProductModel',
                'level'          => 1,
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
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
                'identifier'     => 'model-biker-jacket-leather',
                'type'           => 'PimCatalogSubProductModel',
                'level'          => 1,
                'family_variant' => 'clothing_material_size',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['material'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'la jacket du biker ouaip ouaip',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                ],
            ],
            [
                'identifier'     => 'model-biker-jacket-polyester',
                'type'           => 'PimCatalogSubProductModel',
                'level'          => 1,
                'family_variant' => 'clothing_material_size',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['material'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'la jacket du biker ouaip ouaip',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'material-option'  => [
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
                'identifier'     => 'tshirt-grey-s',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'grey',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-grey.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-grey-m',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'grey',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-grey.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-grey-l',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'grey',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-grey.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-grey-xl',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'grey',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-grey.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-blue-s',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-blue.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-blue-m',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-blue.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-blue-l',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-blue.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-blue-xl',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-blue.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-red-s',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-red.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-red-m',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-red.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-red-l',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-red.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-red-xl',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_color_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-red.jpg',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'tshirt-unique-color-s',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier'     => 'tshirt-unique-color-m',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'     => 'tshirt-unique-color-l',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier'     => 'tshirt-unique-color-xl',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_size',
                'family'         => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            // Watch
            [
                'identifier' => 'watch',
                'type'       => 'PimCatalogProduct',
                'family_variant' => null,
                'family'     => [
                    'code'   => 'watch',
                    'labels' => [
                        'fr_FR' => 'La montre unique',
                    ],
                ],
                'owned_attributes' => ['description', 'color'],
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
                'identifier'     => 'hat-m',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'accessories_size',
                'family'         => [
                    'code'   => 'accessories',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'     => 'hat-l',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'accessories_size',
                'family'         => [
                    'code'   => 'accessories',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],

            // Tshirt unique size model
            [
                'identifier'     => 'tshirt-unique-size-blue',
                'type'           => 'PimCatalogProductVariant',
                'level'          => 0,
                'family_variant' => 'clothing_color',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-unique-size-blue.jpg',
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
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
            ],

            [
                'identifier'     => 'tshirt-unique-size-red',
                'type'           => 'PimCatalogProductVariant',
                'level'          => 0,
                'family_variant' => 'clothing_color',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-unique-size-red.jpg',
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
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                ],
            ],

            [
                'identifier'     => 'tshirt-unique-size-yellow',
                'type'           => 'PimCatalogProductVariant',
                'level'          => 0,
                'family_variant' => 'clothing_color',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'tshirt-unique-size-yellow.jpg',
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
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'yellow',
                        ],
                    ],
                ],
            ],

            // Running shoes
            [
                'identifier'     => 'running-shoes-s-white',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                ],
            ],

            [
                'identifier'     => 'running-shoes-s-blue',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
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
                'identifier'     => 'running-shoes-s-red',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
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
                'identifier'     => 'running-shoes-m-white',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                ],
            ],

            [
                'identifier'     => 'running-shoes-m-blue',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
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
                'identifier'     => 'running-shoes-m-red',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
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
                'identifier'     => 'running-shoes-l-white',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                ],
            ],

            [
                'identifier'     => 'running-shoes-l-blue',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
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
                'identifier'     => 'running-shoes-l-red',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'shoes_size_color',
                'family'         => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['color'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'such beautiful shoes',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
                        ],
                    ],
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'red',
                        ],
                    ],
                ],
            ],

            // Biker
            [
                'identifier'     => 'biker-jacket-leather-s',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_material_size',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'la jacket du biker ouaip ouaip',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
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
                'identifier'     => 'biker-jacket-leather-m',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_material_size',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'la jacket du biker ouaip ouaip',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
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
                'identifier'     => 'biker-jacket-leather-l',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_material_size',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'la jacket du biker ouaip ouaip',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'leather',
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
                'identifier'     => 'biker-jacket-polyester-s',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_material_size',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'la jacket du biker ouaip ouaip',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'biker-jacket-polyester-m',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_material_size',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'la jacket du biker ouaip ouaip',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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
                'identifier'     => 'biker-jacket-polyester-l',
                'type'           => 'PimCatalogProductVariant',
                'family_variant' => 'clothing_material_size',
                'family'         => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'owned_attributes' => ['size'],
                'values'         => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'la jacket du biker ouaip ouaip',
                        ],
                    ],
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'white',
                        ],
                    ],
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'polyester',
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

        $this->indexThings($rootProductModels, self::DOCUMENT_TYPE);
        $this->indexThings($subProductModels, self::DOCUMENT_TYPE);
        $this->indexThings($productVariants, self::DOCUMENT_TYPE);
    }

    /**
     * Indexes the given list of products
     *
     * @param array $things
     * @param       $indexType
     */
    protected function indexThings(array $things, $indexType)
    {
        foreach ($things as $thing) {
            $this->esClient->index(
                $indexType,
                $thing['identifier'],
                $thing,
                null,
                null
            );
        }

        $this->esClient->refreshIndex();
    }
}
