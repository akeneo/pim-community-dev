<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * Dataset used to test the search of:
 * - products only (export builder use case)
 * - products and product models (Datagrid use case aka smart search)
 *
 * Here are the families and family variants used to create those products and product models:
 *
 * |-------------|---------------------------------------------|------------------------|---------------------------------------|---------------------------------|--------------------------------|
 * | family      | Attributes of the family                    | family variant         | common attributes                     | level 1                         | level 2                        |
 * |-------------|---------------------------------------------|------------------------|---------------------------------------|---------------------------------|--------------------------------|
 * | clothing    | description, color, image_1, material, size | clothing_color_size    | description                           | color (axis), image_1, material | size (axis)                    |
 * | clothing    | description, color, image_1, material, size | clothing_size          | description, image_1, color, material | size (axis)                     | -                              |
 * | clothing    | description, color, image_1, material, size | clothing_color         | description, size                     | color (axis), image_1,material  | -                              |
 * | accessories | description, color, material, size          | accessories_size       | description, color, material          | size (axis)                     | -                              |
 * | accessories | -                                           | -                      | -                                     | -                               | -                              |
 * | shoes       | description, size, color, image_1, material | shoes_size_color       | description                           | size (axis)                     | color (axis) image_1, material |
 * | clothing    | description, color, image_1, material, size | clothing_material_size | description                           | material (axis), image_1, color | size (axis)                    |
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 *
 * And here are the products and product models linked to those family variants:
 *
 * |------------------------------------------------------------------------------------------------------------------------------------|
 * | Label                        | Color  | Size | Material  | Completeness | Complete products | Family      | Family variant         |
 * |------------------------------|--------|------|-----------|--------------|-------------------|-------------|------------------------|
 * | model-tshirt                 |        |      |           |              | 7/12              | clothing    | clothing_color_size    |
 * | model-tshirt-grey            | grey   |      | Cotton    |              | 4/4               | clothing    | clothing_color_size    |
 * | tshirt-grey-s                | grey   | S    | Cotton    | 100%         |                   | clothing    | clothing_color_size    |
 * | tshirt-grey-m                | grey   | M    | Cotton    | 100%         |                   | clothing    | clothing_color_size    |
 * | tshirt-grey-l                | grey   | L    | Cotton    | 100%         |                   | clothing    | clothing_color_size    |
 * | tshirt-grey-xl               | grey   | XL   | Cotton    | 100%         |                   | clothing    | clothing_color_size    |
 * | model-tshirt-blue            | blue   |      | Polyester |              | 3/4               | clothing    | clothing_color_size    |
 * | tshirt-blue-s                | blue   | S    | Polyester | 80%          |                   | clothing    | clothing_color_size    |
 * | tshirt-blue-m                | blue   | M    | Polyester | 100%         |                   | clothing    | clothing_color_size    |
 * | tshirt-blue-l                | blue   | L    | Polyester | 100%         |                   | clothing    | clothing_color_size    |
 * | tshirt-blue-xl               | blue   | XL   | Polyester | 100%         |                   | clothing    | clothing_color_size    |
 * | model-tshirt-red             | red    |      | Cotton    |              | 0/4               | clothing    | clothing_color_size    |
 * | tshirt-red-s                 | red    | S    | Cotton    | 70%          |                   | clothing    | clothing_color_size    |
 * | tshirt-red-m                 | red    | M    | Cotton    | 70%          |                   | clothing    | clothing_color_size    |
 * | tshirt-red-l                 | red    | L    | Cotton    | 60%          |                   | clothing    | clothing_color_size    |
 * | tshirt-red-xl                | red    | XL   | Cotton    | 80%          |                   | clothing    | clothing_color_size    |
 * |------------------------------------------------------------------------------------------------------------------------------------|
 * | model-tshirt-unique-color    | red    |      | Cotton    |              | 2/4               | clothing    | clothing_size          |
 * | tshirt-unique-color-s        | red    | S    | Cotton    | 100%         |                   | clothing    | clothing_size          |
 * | tshirt-unique-color-m        | red    | M    | Cotton    | 100%         |                   | clothing    | clothing_size          |
 * | tshirt-unique-color-l        | red    | L    | Cotton    | 50%          |                   | clothing    | clothing_size          |
 * | tshirt-unique-color-xl       | red    | XL   | Cotton    | 60%          |                   | clothing    | clothing_size          |
 * |------------------------------------------------------------------------------------------------------------------------------------|
 * | watch                        | blue   |      | Metal     | 0%           |                   | clothing    | clothing_size          |
 * |------------------------------------------------------------------------------------------------------------------------------------|
 * | model-hat                    | grey   |      | Wool      |              | 2/2               | accessories | accessories_size       |
 * | hat-m                        | grey   | M    | Wool      | 100%         |                   | accessories | accessories_size       |
 * | hat-l                        | grey   | L    | Wool      | 100%         |                   | accessories | accessories_size       |
 * |------------------------------------------------------------------------------------------------------------------------------------|
 * | model-tshirt-unique-size     |        | U    | Cotton    |              | 1/3               | clothing    | clothing_color         |
 * | tshirt-unique-size-blue      | blue   | U    | Cotton    | 100%         |                   | clothing    | clothing_color         |
 * | tshirt-unique-size-red       | red    | U    | Cotton    | 70%          |                   | clothing    | clothing_color         |
 * | tshirt-unique-size-yellow    | yellow | U    | Cotton    | 50%          |                   | clothing    | clothing_color         |
 * |------------------------------------------------------------------------------------------------------------------------------------|
 * | model-running-shoes          |        |      | Leather   |              | 4/9               | shoes       | shoes_size_color       |
 * | model-running-shoes-s        |        | S    | Leather   |              | 3/3               | shoes       | shoes_size_color       |
 * | running-shoes-s-white        | white  | S    | Leather   | 100%         |                   | shoes       | shoes_size_color       |
 * | running-shoes-s-blue         | blue   | S    | Leather   | 100%         |                   | shoes       | shoes_size_color       |
 * | running-shoes-s-red          | red    | S    | Leather   | 100%         |                   | shoes       | shoes_size_color       |
 * | model-running-shoes-m        |        | M    | Leather   |              | 0/3               | shoes       | shoes_size_color       |
 * | running-shoes-m-white        | white  | M    | Leather   | 0%           |                   | shoes       | shoes_size_color       |
 * | running-shoes-m-blue         | blue   | M    | Leather   | 0%           |                   | shoes       | shoes_size_color       |
 * | running-shoes-m-red          | red    | M    | Leather   | 0%           |                   | shoes       | shoes_size_color       |
 * | model-running-shoes-l        |        | L    | Leather   |              | 1/3               | shoes       | shoes_size_color       |
 * | running-shoes-l-white        | white  | L    | Leather   | 60%          |                   | shoes       | shoes_size_color       |
 * | running-shoes-l-blue         | blue   | L    | Leather   | 70%          |                   | shoes       | shoes_size_color       |
 * | running-shoes-l-red          | red    | L    | Leather   | 100%         |                   | shoes       | shoes_size_color       |
 * |------------------------------------------------------------------------------------------------------------------------------------|
 * | model-biker-jacket           | white  |      |           |              | 0/6               | clothing    | clothing_material_size |
 * | model-biker-jacket-leather   | white  |      | Leather   |              | 0/3               | clothing    | clothing_material_size |
 * | biker-jacket-leather-s       | white  | S    | Leather   | 0%           |                   | clothing    | clothing_material_size |
 * | biker-jacket-leather-m       | white  | M    | Leather   | 0%           |                   | clothing    | clothing_material_size |
 * | biker-jacket-leather-l       | white  | L    | Leather   | 0%           |                   | clothing    | clothing_material_size |
 * | model-biker-jacket-polyester | white  |      | Polyester |              | 0/3               | clothing    | clothing_material_size |
 * | biker-jacket-polyester-s     | white  | S    | Polyester | 20%          |                   | clothing    | clothing_material_size |
 * | biker-jacket-polyester-m     | white  | M    | Polyester | 30%          |                   | clothing    | clothing_material_size |
 * | biker-jacket-polyester-l     | white  | L    | Polyester | 20%          |                   | clothing    | clothing_material_size |
 * --------------------------------------------------------------------------------------------------------------------------------------
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractPimCatalogProductModelIntegration extends AbstractPimCatalogTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function addProducts()
    {
        $rootProductModels = [
            // simple tshirt
            [
                'identifier'                => 'model-tshirt',
                'product_type'              => 'PimCatalogRootProductModel',
                'level'                     => 2,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['description'],
                'values'                    => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'Cotton t-shirt with a round neck Divided',
                        ],
                    ],
                ],
            ],

            // Tshirt unique color model
            [
                'identifier'                => 'model-tshirt-unique-color',
                'product_type'              => 'PimCatalogRootProductModel',
                'level'                     => 1,
                'family_variant'            => 'clothing_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['description', 'color', 'material'],
                'values'                    => [
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
                'identifier'                => 'model-hat',
                'product_type'              => 'PimCatalogRootProductModel',
                'level'                     => 1,
                'family_variant'            => 'accessories_size',
                'family'                    => [
                    'code'   => 'accessories',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'attributes_for_this_level' => ['description', 'color', 'material'],
                'values'                    => [
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
                'identifier'                => 'model-tshirt-unique-size',
                'product_type'              => 'PimCatalogRootProductModel',
                'level'                     => 1,
                'family_variant'            => 'clothing_color',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['description', 'size', 'material'],
                'values'                    => [
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
                    'material-option'  => [
                        '<all_channels>' => [
                            '<all_locales>' => 'cotton',
                        ],
                    ],
                ],
            ],

            // Running shoes
            [
                'identifier'                => 'model-running-shoes',
                'product_type'              => 'PimCatalogRootProductModel',
                'level'                     => 2,
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['description', 'material'],
                'values'                    => [
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
                ],
            ],

            // Biker jacket
            [
                'identifier'                => 'model-biker-jacket',
                'product_type'              => 'PimCatalogRootProductModel',
                'level'                     => 2,
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['description', 'color'],
                'values'                    => [
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
                'identifier'                => 'model-tshirt-grey',
                'product_type'              => 'PimCatalogSubProductModel',
                'level'                     => 1,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['color', 'material'],
                'values'                    => [
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
                'identifier'                => 'model-tshirt-blue',
                'product_type'              => 'PimCatalogSubProductModel',
                'level'                     => 1,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['color', 'material'],
                'values'                    => [
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
                'identifier'                => 'model-tshirt-red',
                'product_type'              => 'PimCatalogSubProductModel',
                'level'                     => 1,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['color', 'material'],
                'values'                    => [
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
                'identifier'                => 'model-running-shoes-s',
                'product_type'              => 'PimCatalogSubProductModel',
                'level'                     => 1,
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'model-running-shoes-m',
                'product_type'              => 'PimCatalogSubProductModel',
                'level'                     => 1,
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'model-running-shoes-l',
                'product_type'              => 'PimCatalogSubProductModel',
                'level'                     => 1,
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'model-biker-jacket-leather',
                'product_type'              => 'PimCatalogSubProductModel',
                'level'                     => 1,
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['material'],
                'values'                    => [
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
                'identifier'                => 'model-biker-jacket-polyester',
                'product_type'              => 'PimCatalogSubProductModel',
                'level'                     => 1,
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['material'],
                'values'                    => [
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

        $variantProducts = [
            // tshirt variants (level 2: varying on color and size)
            [
                'identifier'                => 'tshirt-grey-s',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier'                => 'tshirt-grey-m',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'                => 'tshirt-grey-l',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier'                => 'tshirt-grey-xl',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            [
                'identifier'                => 'tshirt-blue-s',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'tshirt-blue-m',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'tshirt-blue-l',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'tshirt-blue-xl',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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

            [
                'identifier'                => 'tshirt-red-s',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 's',
                        ],
                    ],
                ],
            ],
            [
                'identifier'                => 'tshirt-red-m',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'm',
                        ],
                    ],
                ],
            ],
            [
                'identifier'                => 'tshirt-red-l',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'l',
                        ],
                    ],
                ],
            ],
            [
                'identifier'                => 'tshirt-red-xl',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                    'size-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'xl',
                        ],
                    ],
                ],
            ],

            // T-shirt: size
            [
                'identifier'                => 'tshirt-unique-color-s',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'tshirt-unique-color-m',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'tshirt-unique-color-l',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'tshirt-unique-color-xl',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'watch',
                'product_type'              => 'PimCatalogProduct',
                'family_variant'            => null,
                'family'                    => [
                    'code'   => 'watch',
                    'labels' => [
                        'fr_FR' => 'La montre unique',
                    ],
                ],
                'attributes_for_this_level' => ['description', 'color'],
                'values'                    => [
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
                'identifier'                => 'hat-m',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'accessories_size',
                'family'                    => [
                    'code'   => 'accessories',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'hat-l',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'accessories_size',
                'family'                    => [
                    'code'   => 'accessories',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'tshirt-unique-size-blue',
                'product_type'              => 'PimCatalogVariantProduct',
                'level'                     => 0,
                'family_variant'            => 'clothing_color',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'tshirt-unique-size-red',
                'product_type'              => 'PimCatalogVariantProduct',
                'level'                     => 0,
                'family_variant'            => 'clothing_color',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'tshirt-unique-size-yellow',
                'product_type'              => 'PimCatalogVariantProduct',
                'level'                     => 0,
                'family_variant'            => 'clothing_color',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'running-shoes-s-white',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'running-shoes-s-blue',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'running-shoes-s-red',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'running-shoes-m-white',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'running-shoes-m-blue',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'running-shoes-m-red',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'running-shoes-l-white',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'running-shoes-l-blue',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'running-shoes-l-red',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['color'],
                'values'                    => [
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
                'identifier'                => 'biker-jacket-leather-s',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'biker-jacket-leather-m',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'biker-jacket-leather-l',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'biker-jacket-polyester-s',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'biker-jacket-polyester-m',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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
                'identifier'                => 'biker-jacket-polyester-l',
                'product_type'              => 'PimCatalogVariantProduct',
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'attributes_for_this_level' => ['size'],
                'values'                    => [
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

        $this->indexProductDocuments(array_merge($rootProductModels, $subProductModels, $variantProducts));
    }
}
