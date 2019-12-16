<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

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
 * | clothing    | description, color, image_1, material, size | clothing_color_size    | description                           | color (axis), image_1, material | size (axis), weight            |
 * | clothing    | description, color, image_1, material, size | clothing_size          | description, image_1, color, material | size (axis)                     | -                              |
 * | clothing    | description, color, image_1, material, size | clothing_color         | description, size, material           | color (axis), image_1           | -                              |
 * | accessories | description, color, material, size          | accessories_size       | description, color, material          | size (axis)                     | -                              |
 * | accessories | -                                           | -                      | -                                     | -                               | -                              |
 * | shoes       | description, size, color, image_1, material | shoes_size_color       | description                           | size (axis)                     | color (axis), image_1, material|
 * | clothing    | description, color, image_1, material, size | clothing_material_size | description, color                    | material (axis), image_1,       | size (axis), weight            |
 * | camera      | brand                                       |                        | brand                                 |                                 |                                |
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------
 *
 * And here are the products and product models linked to those family variants:
 *
 * |--------------------------|-------------------------------------------------------------------------------------------------------------------------------------------------------|
 * | Id                       | Label                        | categories       | Color  | Size | Material  | Brand       | Completeness | Complete products | Family      | Family variant         |
 * |--------------------------|------------------------------|------------------|--------|------|-----------|-------------|--------------|-------------------|-------------|------------------------|
 * | product_model_1          | model-tshirt                 |                  |        |      |           |             |              | 7/12              | clothing    | clothing_color_size    |
 * | product_model_2          | model-tshirt-grey            |                  | grey   |      | Cotton    |             |              | 4/4               | clothing    | clothing_color_size    |
 * | product_1                | tshirt-grey-s                |                  | grey   | S    | Cotton    |             | 100%         |                   | clothing    | clothing_color_size    |
 * | product_2                | tshirt-grey-m                |                  | grey   | M    | Cotton    |             | 100%         |                   | clothing    | clothing_color_size    |
 * | product_3                | tshirt-grey-l                |                  | grey   | L    | Cotton    |             | 100%         |                   | clothing    | clothing_color_size    |
 * | product_4                | tshirt-grey-xl               |                  | grey   | XL   | Cotton    |             | 100%         |                   | clothing    | clothing_color_size    |
 * | product_model_3          | model-tshirt-blue            |                  | blue   |      | Polyester |             |              | 3/4               | clothing    | clothing_color_size    |
 * | product_5                | tshirt-blue-s                |                  | blue   | S    | Polyester |             | 80%          |                   | clothing    | clothing_color_size    |
 * | product_6                | tshirt-blue-m                |                  | blue   | M    | Polyester |             | 100%         |                   | clothing    | clothing_color_size    |
 * | product_7                | tshirt-blue-l                |                  | blue   | L    | Polyester |             | 100%         |                   | clothing    | clothing_color_size    |
 * | product_8                | tshirt-blue-xl               |                  | blue   | XL   | Polyester |             | 100%         |                   | clothing    | clothing_color_size    |
 * | product_model_4          | model-tshirt-red             |                  | red    |      | Cotton    |             |              | 0/4               | clothing    | clothing_color_size    |
 * | product_9                | tshirt-red-s                 |                  | red    | S    | Cotton    |             | 70%          |                   | clothing    | clothing_color_size    |
 * | product_10               | tshirt-red-m                 |                  | red    | M    | Cotton    |             | 70%          |                   | clothing    | clothing_color_size    |
 * | product_11               | tshirt-red-l                 |                  | red    | L    | Cotton    |             | 60%          |                   | clothing    | clothing_color_size    |
 * | product_12               | tshirt-red-xl                |                  | red    | XL   | Cotton    |             | 80%          |                   | clothing    | clothing_color_size    |
 * |--------------------------|------------------------------|------------------|---------------------------|-------------|-------------------------------------------------------------------------|
 * | product_model_5          | model-tshirt-unique-color    |                  | red    |      | Cotton    |             |              | 2/4               | clothing    | clothing_size          |
 * | product_13               | tshirt-unique-color-s        |                  | red    | S    | Cotton    |             | 100%         |                   | clothing    | clothing_size          |
 * | product_14               | tshirt-unique-color-m        |                  | red    | M    | Cotton    |             | 100%         |                   | clothing    | clothing_size          |
 * | product_15               | tshirt-unique-color-l        |                  | red    | L    | Cotton    |             | 50%          |                   | clothing    | clothing_size          |
 * | product_16               | tshirt-unique-color-xl       |                  | red    | XL   | Cotton    |             | 60%          |                   | clothing    | clothing_size          |
 * |--------------------------|------------------------------|------------------|---------------------------|-------------|-------------------------------------------------------------------------|
 * | product_17               | watch                        |                  | blue   |      | Metal     |             | 0%           |                   | clothing    | clothing_size          |
 * |--------------------------|------------------------------|------------------|---------------------------|-------------|-------------------------------------------------------------------------|
 * | product_model_6          | model-hat                    |                  | grey   |      | Wool      |             |              | 2/2               | accessories | accessories_size       |
 * | product_18               | hat-m                        |                  | grey   | M    | Wool      |             | 100%         |                   | accessories | accessories_size       |
 * | product_19               | hat-l                        |                  | grey   | L    | Wool      |             | 100%         |                   | accessories | accessories_size       |
 * |--------------------------|------------------------------|------------------|---------------------------|-------------|-------------------------------------------------------------------------|
 * | product_model_7          | model-tshirt-unique-size     |                  |        | U    | Cotton    |             |              | 1/3               | clothing    | clothing_color         |
 * | product_20               | tshirt-unique-size-blue      |                  | blue   | U    | Cotton    |             | 100%         |                   | clothing    | clothing_color         |
 * | product_21               | tshirt-unique-size-red       |                  | red    | U    | Cotton    |             | 70%          |                   | clothing    | clothing_color         |
 * | product_22               | tshirt-unique-size-yellow    |                  | yellow | U    | Cotton    |             | 50%          |                   | clothing    | clothing_color         |
 * |--------------------------|------------------------------|------------------|---------------------------|-------------|-------------------------------------------------------------------------|
 * | product_model_8          | model-running-shoes          | shoes            |        |      | Leather   |             |              | 4/9               | shoes       | shoes_size_color       |
 * | product_model_9          | model-running-shoes-s        | shoes            |        | S    | Leather   |             |              | 3/3               | shoes       | shoes_size_color       |
 * | product_23               | running-shoes-s-white        | shoes,men,women  | white  | S    | Leather   |             | 100%         |                   | shoes       | shoes_size_color       |
 * | product_24               | running-shoes-s-blue         | shoes,men        | blue   | S    | Leather   |             | 100%         |                   | shoes       | shoes_size_color       |
 * | product_25               | running-shoes-s-red          | shoes,women      | red    | S    | Leather   |             | 100%         |                   | shoes       | shoes_size_color       |
 * | product_model_10         | model-running-shoes-m        | shoes            |        | M    | Leather   |             |              | 0/3               | shoes       | shoes_size_color       |
 * | product_26               | running-shoes-m-white        | shoes,men,women  | white  | M    | Leather   |             | 0%           |                   | shoes       | shoes_size_color       |
 * | product_27               | running-shoes-m-blue         | shoes,men        | blue   | M    | Leather   |             | 0%           |                   | shoes       | shoes_size_color       |
 * | product_28               | running-shoes-m-red          | shoes,women      | red    | M    | Leather   |             | 0%           |                   | shoes       | shoes_size_color       |
 * | product_model_11         | model-running-shoes-l        | shoes            |        | L    | Leather   |             |              | 1/3               | shoes       | shoes_size_color       |
 * | product_29               | running-shoes-l-white        | shoes,men,women  | white  | L    | Leather   |             | 60%          |                   | shoes       | shoes_size_color       |
 * | product_30               | running-shoes-l-blue         | shoes,men        | blue   | L    | Leather   |             | 70%          |                   | shoes       | shoes_size_color       |
 * | product_31               | running-shoes-l-red          | shoes,women      | red    | L    | Leather   |             | 100%         |                   | shoes       | shoes_size_color       |
 * |--------------------------|------------------------------|------------------|---------------------------|-------------|-------------------------------------------------------------------------|
 * | product_model_12         | model-biker-jacket           |                  | white  |      |           |             |              | 0/6               | clothing    | clothing_material_size |
 * | product_model_13         | model-biker-jacket-leather   |                  | white  |      | Leather   |             |              | 0/3               | clothing    | clothing_material_size |
 * | product_32               | biker-jacket-leather-s       |                  | white  | S    | Leather   |             | 0%           |                   | clothing    | clothing_material_size |
 * | product_33               | biker-jacket-leather-m       |                  | white  | M    | Leather   |             | 0%           |                   | clothing    | clothing_material_size |
 * | product_34               | biker-jacket-leather-l       |                  | white  | L    | Leather   |             | 0%           |                   | clothing    | clothing_material_size |
 * | product_model_14         | model-biker-jacket-polyester |                  | white  |      | Polyester |             |              | 0/3               | clothing    | clothing_material_size |
 * | product_35               | biker-jacket-polyester-s     |                  | white  | S    | Polyester |             | 19%          |                   | clothing    | clothing_material_size |
 * | product_36               | biker-jacket-polyester-m     |                  | white  | M    | Polyester |             | 30%          |                   | clothing    | clothing_material_size |
 * | product_37               | biker-jacket-polyester-l     |                  | white  | L    | Polyester |             | 20%          |                   | clothing    | clothing_material_size |
 * |--------------------------|------------------------------|------------------|--------|------|-----------|-------------|-------------------------------------------------------------------------|
 * | product_38               | camera_nikon                 |                  |        |      |           | Nike        |              |                   | camera      | clothing_material_size |
 * | product_39               | empty_product                |                  |        |      |           |             |              |                   | camera      | clothing_material_size |
 * -------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
abstract class AbstractPimCatalogProductModel extends AbstractPimCatalogTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function addDocuments()
    {
        $rootProductModels = [
            // simple tshirt
            [
                'id'                        => 'product_model_1',
                'identifier'                => 'model-tshirt',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 2,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_of_ancestors' => [],
                'categories_of_ancestors' => [],
                'attributes_for_this_level' => ['description'],
                'parent'                    => null,
                'ancestors'                 => [
                    'codes' => null,
                    'ids'   => null,
                ],
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
                'id'                        => 'product_model_5',
                'identifier'                => 'model-tshirt-unique-color',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 1,
                'family_variant'            => 'clothing_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_of_ancestors' => [],
                'categories_of_ancestors' => [],
                'attributes_for_this_level' => ['description', 'color', 'image', 'material'],
                'parent'                    => null,
                'ancestors'                 => [
                    'codes' => null,
                    'ids'   => null,
                ],
                'values'                    => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-rockstar.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-rockstar.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                'id'                        => 'product_model_6',
                'identifier'                => 'model-hat',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 1,
                'family_variant'            => 'accessories_size',
                'family'                    => [
                    'code'   => 'accessories',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'attributes_of_ancestors' => [],
                'categories_of_ancestors' => [],
                'attributes_for_this_level' => ['description', 'color', 'material'],
                'parent'                    => null,
                'ancestors'                 => [
                    'codes' => null,
                    'ids'   => null,
                ],
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
                'id'                        => 'product_model_7',
                'identifier'                => 'model-tshirt-unique-size',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 1,
                'family_variant'            => 'clothing_color',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'attributes_of_ancestors' => [],
                'categories_of_ancestors' => [],
                'attributes_for_this_level' => ['description', 'size', 'material'],
                'parent'                    => null,
                'ancestors'                 => [
                    'codes' => null,
                    'ids'   => null,
                ],
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
                'id'                        => 'product_model_8',
                'identifier'                => 'model-running-shoes',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 2,
                'family_variant'            => 'shoes_size_color',
                'categories'                => ['shoes'],
                'categories_of_ancestors'      => [],
                'attributes_of_ancestors'      => [],
                'attributes_for_this_level' => ['description', 'material'],
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => null,
                'ancestors'                 => [
                    'codes' => null,
                    'ids'   => null,
                ],
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
                'id'                        => 'product_model_12',
                'identifier'                => 'model-biker-jacket',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 2,
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'categories_of_ancestors'      => [],
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['description', 'color'],
                'parent'                    => null,
                'ancestors'                 => [
                    'codes' => null,
                    'ids'   => null,
                ],
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
                'id'                        => 'product_model_2',
                'identifier'                => 'model-tshirt-grey',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 1,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors'      => [],
                'attributes_of_ancestors' => ['description'],
                'attributes_for_this_level' => ['color', 'image', 'material'],
                'parent'                    => 'model-tshirt',
                'ancestors'                 => [
                    'codes' => ['model-tshirt'],
                    'ids'   => ['product_model_1'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-grey.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-grey.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                'id'                        => 'product_model_3',
                'identifier'                => 'model-tshirt-blue',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 1,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors'      => [],
                'attributes_of_ancestors' => ['description'],
                'attributes_for_this_level' => ['color', 'image', 'material'],
                'parent'                    => 'model-tshirt',
                'ancestors'                 => [
                    'codes' => ['model-tshirt'],
                    'ids'   => ['product_model_1'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-blue.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-blue.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                'id'                        => 'product_model_4',
                'identifier'                => 'model-tshirt-red',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 1,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors'   => [],
                'attributes_of_ancestors'   => ['description'],
                'attributes_for_this_level' => ['color', 'image', 'material'],
                'parent'                    => 'model-tshirt',
                'ancestors'                 => [
                    'codes' => ['model-tshirt'],
                    'ids'   => ['product_model_1'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-red.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-red.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                'id'                        => 'product_model_9',
                'identifier'                => 'model-running-shoes-s',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 1,
                'categories'                => ['shoes'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material'],
                'attributes_for_this_level' => ['size'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes'],
                    'ids'   => ['product_model_8'],
                ],
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
                ],
            ],

            [
                'id'                        => 'product_model_10',
                'identifier'                => 'model-running-shoes-m',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 1,
                'categories'                => ['shoes'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material'],
                'attributes_for_this_level' => ['size'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes'],
                    'ids'   => ['product_model_8'],
                ],
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
                ],
            ],

            [
                'id'                        => 'product_model_11',
                'identifier'                => 'model-running-shoes-l',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 1,
                'categories'                => ['shoes'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material'],
                'attributes_for_this_level' => ['size'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes'],
                    'ids'   => ['product_model_8'],
                ],
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
                ],
            ],

            [
                'id'                        => 'product_model_13',
                'identifier'                => 'model-biker-jacket-leather',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 1,
                'categories'                => [],
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'color'],
                'attributes_for_this_level' => ['material'],
                'parent'                    => 'model-biker-jacket',
                'ancestors'                 => [
                    'codes' => ['model-biker-jacket'],
                    'ids'   => ['product_model_12'],
                ],
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
                'id'                        => 'product_model_14',
                'identifier'                => 'model-biker-jacket-polyester',
                'document_type'             => ProductModelInterface::class,
                'level'                     => 1,
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'color'],
                'attributes_for_this_level' => ['material'],
                'parent'                    => 'model-biker-jacket',
                'ancestors'                 => [
                    'codes' => ['model-biker-jacket'],
                    'ids'   => ['product_model_12'],
                ],
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
                'id'                        => 'product_1',
                'identifier'                => 'tshirt-grey-s',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-grey',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-grey'],
                    'ids'   => ['product_model_1', 'product_model_2'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-grey.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-grey.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '170',
                                'base_data' => '0.17',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],
            [
                'id'                        => 'product_2',
                'identifier'                => 'tshirt-grey-m',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-grey',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-grey'],
                    'ids'   => ['product_model_1', 'product_model_2'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-grey.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-grey.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '200',
                                'base_data' => '0.20',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],
            [
                'id'                        => 'product_3',
                'identifier'                => 'tshirt-grey-l',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-grey',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-grey'],
                    'ids'   => ['product_model_1', 'product_model_2'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-grey.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-grey.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '230',
                                'base_data' => '0.23',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],
            [
                'id'                        => 'product_3',
                'identifier'                => 'tshirt-grey-xl',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-grey',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-grey'],
                    'ids'   => ['product_model_1', 'product_model_2'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-grey.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-grey.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '260',
                                'base_data' => '0.26',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],

            [
                'id'                        => 'product_5',
                'identifier'                => 'tshirt-blue-s',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-blue',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-blue'],
                    'ids'   => ['product_model_1', 'product_model_3'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-blue.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-blue.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '170',
                                'base_data' => '0.17',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],
            [
                'id'                        => 'product_6',
                'identifier'                => 'tshirt-blue-m',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-blue',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-blue'],
                    'ids'   => ['product_model_1', 'product_model_3'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-blue.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-blue.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '200',
                                'base_data' => '0.2',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],
            [
                'id'                        => 'product_7',
                'identifier'                => 'tshirt-blue-l',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-blue',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-blue'],
                    'ids'   => ['product_model_1', 'product_model_3'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-blue.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-blue.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '230',
                                'base_data' => '0.23',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],
            [
                'id'                        => 'product_8',
                'identifier'                => 'tshirt-blue-xl',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-blue',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-blue'],
                    'ids'   => ['product_model_1', 'product_model_3'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-blue.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-blue.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '260',
                                'base_data' => '0.26',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],

            [
                'id'                        => 'product_9',
                'identifier'                => 'tshirt-red-s',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-red',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-red'],
                    'ids'   => ['product_model_1', 'product_model_4'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-red.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-red.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '170',
                                'base_data' => '0.17',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],
            [
                'id'                        => 'product_10',
                'identifier'                => 'tshirt-red-m',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-red',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-red'],
                    'ids'   => ['product_model_1', 'product_model_4'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-red.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-red.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '200',
                                'base_data' => '0.2',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],
            [
                'id'                        => 'product_11',
                'identifier'                => 'tshirt-red-l',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-red',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-red'],
                    'ids'   => ['product_model_1', 'product_model_4'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-red.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-red.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '230',
                                'base_data' => '0.23',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],
            [
                'id'                        => 'product_12',
                'identifier'                => 'tshirt-red-xl',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_color_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['color', 'image', 'material', 'description'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-red',
                'ancestors'                 => [
                    'codes' => ['model-tshirt', 'model-tshirt-red'],
                    'ids'   => ['product_model_1', 'product_model_4'],
                ],
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
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-red.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-red.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '260',
                                'base_data' => '0.26',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],

            // T-shirt: size
            [
                'id'                        => 'product_13',
                'identifier'                => 'tshirt-unique-color-s',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'color', 'material'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-unique-color',
                'ancestors'                 => [
                    'codes' => ['model-tshirt-unique-color'],
                    'ids'   => ['product_model_5'],
                ],
                'values'                    => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-rockstar.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-rockstar.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                'id'                        => 'product_14',
                'identifier'                => 'tshirt-unique-color-m',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'color', 'material'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-unique-color',
                'ancestors'                 => [
                    'codes' => ['model-tshirt-unique-color'],
                    'ids'   => ['product_model_5'],
                ],
                'values'                    => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-rockstar.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-rockstar.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                'id'                        => 'product_15',
                'identifier'                => 'tshirt-unique-color-l',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'color', 'material'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-unique-color',
                'ancestors'                 => [
                    'codes' => ['model-tshirt-unique-color'],
                    'ids'   => ['product_model_5'],
                ],
                'values'                    => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-rockstar.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-rockstar.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                'id'                        => 'product_16',
                'identifier'                => 'tshirt-unique-color-xl',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_size',
                'family'                    => [
                    'code'   => 'clothing_size',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'color', 'material'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-tshirt-unique-color',
                'ancestors'                 => [
                    'codes' => ['model-tshirt-unique-color'],
                    'ids'   => ['product_model_5'],
                ],
                'values'                    => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt with a Kurt Cobain print motif',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-rockstar.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-rockstar.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                'id'                        => 'product_17',
                'identifier'                => 'watch',
                'document_type'             => ProductInterface::class,
                'family_variant'            => null,
                'family'                    => [
                    'code'   => 'watch',
                    'labels' => [
                        'fr_FR' => 'La montre unique',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['description', 'color', 'material', 'size'],
                'parent'                    => null,
                'ancestors'                 => [
                    'codes' => null,
                    'ids'   => null,
                ],
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
                'id'                        => 'product_18',
                'identifier'                => 'hat-m',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'accessories_size',
                'family'                    => [
                    'code'   => 'accessories',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'color', 'material'],
                'attributes_for_this_level' => ['size', 'weight'],
                'parent'                    => 'model-hat',
                'ancestors'                 => [
                    'codes' => ['model-hat'],
                    'ids'   => ['product_model_6'],
                ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '150',
                                'base_data' => '0.15',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],
            [
                'id'                        => 'product_19',
                'identifier'                => 'hat-l',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'accessories_size',
                'family'                    => [
                    'code'   => 'accessories',
                    'labels' => [
                        'fr_FR' => 'Famille des chapeaux',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'color', 'material'],
                'attributes_for_this_level' => ['size', 'weight'],
                'parent'                    => 'model-hat',
                'ancestors'                 => [
                    'codes' => ['model-hat'],
                    'ids'   => ['product_model_6'],
                ],
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
                    'weight-metric' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'data'      => '200',
                                'base_data' => '0.2',
                                'unit'      => 'GRAM',
                                'base_unit' => 'KILOGRAM',
                            ]
                        ]
                    ]
                ],
            ],

            // Tshirt unique size model
            [
                'id'                        => 'product_20',
                'identifier'                => 'tshirt-unique-size-blue',
                'document_type'             => ProductInterface::class,
                'level'                     => 0,
                'family_variant'            => 'clothing_color',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'size', 'material'],
                'attributes_for_this_level' => ['color', 'image'],
                'parent'                    => 'model-tshirt-unique-size',
                'ancestors'                 => [
                    'codes' => ['model-tshirt-unique-size'],
                    'ids'   => ['product_model_7'],
                ],
                'values'                    => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-unique-size-blue.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-unique-size-blue.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                'id'                        => 'product_21',
                'identifier'                => 'tshirt-unique-size-red',
                'document_type'             => ProductInterface::class,
                'level'                     => 0,
                'family_variant'            => 'clothing_color',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'size', 'material'],
                'attributes_for_this_level' => ['color', 'image'],
                'parent'                    => 'model-tshirt-unique-size',
                'ancestors'                 => [
                    'codes' => ['model-tshirt-unique-size'],
                    'ids'   => ['product_model_7'],
                ],
                'values'                    => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-unique-size-red.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-unique-size-red.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                'id'                        => 'product_22',
                'identifier'                => 'tshirt-unique-size-yellow',
                'document_type'             => ProductInterface::class,
                'level'                     => 0,
                'family_variant'            => 'clothing_color',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des tshirts',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'size', 'material'],
                'attributes_for_this_level' => ['color', 'image'],
                'parent'                    => 'model-tshirt-unique-size',
                'ancestors'                 => [
                    'codes' => ['model-tshirt-unique-size'],
                    'ids'   => ['product_model_7'],
                ],
                'values'                    => [
                    'description-text' => [
                        '<all_channels>' => [
                            '<all_locales>' => 'T-shirt unique size',
                        ],
                    ],
                    'image-media'      => [
                        '<all_channels>' => [
                            '<all_locales>' =>[
                                'extension' => 'jpg',
                                'key' => 'c/3/7/c/c37cd4f2b1b137fc30c76686a16f85a37b8768a3_tshirt-unique-size-yellow.jpg',
                                'hash' => 'cf2c863861dde58f45bdb32496d42ee3dc2b3c44',
                                'mime_type' => 'image/jpeg',
                                'original_filename' => 'tshirt-unique-size-red.jpg',
                                'size' => 10584,
                                'storage' => 'catalogStorage',
                            ],
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
                'id'                        => 'product_23',
                'identifier'                => 'running-shoes-s-white',
                'document_type'             => ProductInterface::class,
                'categories'                => ['shoes', 'men', 'women'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material', 'size'],
                'attributes_for_this_level' => ['color'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes-s',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes', 'model-running-shoes-s'],
                    'ids'   => ['product_model_8', 'product_model_9'],
                ],
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
                'id'                        => 'product_24',
                'identifier'                => 'running-shoes-s-blue',
                'document_type'             => ProductInterface::class,
                'categories'                => ['shoes', 'men'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material', 'size'],
                'attributes_for_this_level' => ['color'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes-s',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes', 'model-running-shoes-s'],
                    'ids'   => ['product_model_8', 'product_model_9'],
                ],
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
                'id'                        => 'product_25',
                'identifier'                => 'running-shoes-s-red',
                'document_type'             => ProductInterface::class,
                'categories'                => ['shoes', 'women'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material', 'size'],
                'attributes_for_this_level' => ['color'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes-s',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes', 'model-running-shoes-s'],
                    'ids'   => ['product_model_8', 'product_model_9'],
                ],
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
                'id'                        => 'product_26',
                'identifier'                => 'running-shoes-m-white',
                'document_type'             => ProductInterface::class,
                'categories'                => ['shoes', 'men', 'women'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material', 'size'],
                'attributes_for_this_level' => ['color'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes-m',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes', 'model-running-shoes-m'],
                    'ids'   => ['product_model_8', 'product_model_10'],
                ],
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
                'id'                        => 'product_27',
                'identifier'                => 'running-shoes-m-blue',
                'document_type'             => ProductInterface::class,
                'categories'                => ['shoes', 'men'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material', 'size'],
                'attributes_for_this_level' => ['color'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes-m',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes', 'model-running-shoes-m'],
                    'ids'   => ['product_model_8', 'product_model_10'],
                ],
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
                'id'                        => 'product_28',
                'identifier'                => 'running-shoes-m-red',
                'document_type'             => ProductInterface::class,
                'categories'                => ['shoes', 'women'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material', 'size'],
                'attributes_for_this_level' => ['color'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes-m',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes', 'model-running-shoes-m'],
                    'ids'   => ['product_model_8', 'product_model_10'],
                ],
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
                'id'                        => 'product_29',
                'identifier'                => 'running-shoes-l-white',
                'document_type'             => ProductInterface::class,
                'categories'                => ['shoes', 'men', 'women'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material', 'size'],
                'attributes_for_this_level' => ['color'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes-l',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes', 'model-running-shoes-l'],
                    'ids'   => ['product_model_8', 'product_model_11'],
                ],
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
                'id'                        => 'product_30',
                'identifier'                => 'running-shoes-l-blue',
                'document_type'             => ProductInterface::class,
                'categories'                => ['shoes', 'men'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material', 'size'],
                'attributes_for_this_level' => ['color'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes-l',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes', 'model-running-shoes-l'],
                    'ids'   => ['product_model_8', 'product_model_11'],
                ],
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
                'id'                        => 'product_31',
                'identifier'                => 'running-shoes-l-red',
                'document_type'             => ProductInterface::class,
                'categories'                => ['shoes', 'women'],
                'categories_of_ancestors' => ['shoes'],
                'attributes_of_ancestors' => ['description', 'material', 'size'],
                'attributes_for_this_level' => ['color'],
                'family_variant'            => 'shoes_size_color',
                'family'                    => [
                    'code'   => 'shoes',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'parent'                    => 'model-running-shoes-l',
                'ancestors'                 => [
                    'codes' => ['model-running-shoes', 'model-running-shoes-l'],
                    'ids'   => ['product_model_8', 'product_model_11'],
                ],
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
                'id'                        => 'product_32',
                'identifier'                => 'biker-jacket-leather-s',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'material', 'color'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-biker-jacket-leather',
                'ancestors'                 => [
                    'codes' => ['model-biker-jacket', 'model-biker-jacket-leather'],
                    'ids'   => ['product_model_12', 'product_model_13'],
                ],
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
                'id'                        => 'product_33',
                'identifier'                => 'biker-jacket-leather-m',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'material', 'color'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-biker-jacket-leather',
                'ancestors'                 => [
                    'codes' => ['model-biker-jacket', 'model-biker-jacket-leather'],
                    'ids'   => ['product_model_12', 'product_model_13'],
                ],
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
                'id'                        => 'product_34',
                'identifier'                => 'biker-jacket-leather-l',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'material', 'color'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-biker-jacket-leather',
                'ancestors'                 => [
                    'codes' => ['model-biker-jacket', 'model-biker-jacket-leather'],
                    'ids'   => ['product_model_12', 'product_model_13'],
                ],
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
                'id'                        => 'product_35',
                'identifier'                => 'biker-jacket-polyester-s',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'material', 'color'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-biker-jacket-polyester',
                'ancestors'                 => [
                    'codes' => ['model-biker-jacket', 'model-biker-jacket-polyester'],
                    'ids'   => ['product_model_12', 'product_model_14'],
                ],
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
                'id'                        => 'product_36',
                'identifier'                => 'biker-jacket-polyester-m',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'material', 'color'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-biker-jacket-polyester',
                'ancestors'                 => [
                    'codes' => ['model-biker-jacket', 'model-biker-jacket-polyester'],
                    'ids'   => ['product_model_12', 'product_model_14'],
                ],
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
                'id'                        => 'product_37',
                'identifier'                => 'biker-jacket-polyester-l',
                'document_type'             => ProductInterface::class,
                'family_variant'            => 'clothing_material_size',
                'family'                    => [
                    'code'   => 'clothing',
                    'labels' => [
                        'fr_FR' => 'La famille des chaussures de courses',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => ['description', 'material', 'color'],
                'attributes_for_this_level' => ['size'],
                'parent'                    => 'model-biker-jacket-polyester',
                'parent'                    => 'model-biker-jacket-polyester',
                'ancestors'                 => [
                    'codes' => ['model-biker-jacket', 'model-biker-jacket-polyester'],
                    'ids'   => ['product_model_12', 'product_model_14'],
                ],
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

            [
                'id'                      => 'product_40',
                'identifier'              => 'camera_nikon',
                'document_type'           => ProductInterface::class,
                'family'                  => [
                    'code'   => 'camera',
                    'labels' => [
                        'fr_FR' => 'La famille des cameras',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['brand', 'color'],
                'parent'                  => null,
                'values'                  => [
                    'brand-option'      => [
                        '<all_channels>' => [
                            '<all_locales>' => 'nikon',
                        ],
                    ],
                ],
            ],

            [
                'id'                      => 'product_39',
                'identifier'              => 'empty_product',
                'document_type'           => ProductInterface::class,
                'family'                  => [
                    'code'   => 'camera',
                    'labels' => [
                        'fr_FR' => 'La famille des cameras',
                    ],
                ],
                'categories_of_ancestors' => [],
                'attributes_of_ancestors' => [],
                'attributes_for_this_level' => ['brand'],
                'parent'                  => null,
                'values'                  => [],
            ],
        ];

        $this->indexDocuments(array_merge($rootProductModels, $subProductModels, $variantProducts));
    }
}
