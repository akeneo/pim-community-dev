<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

/**
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimCatalogProductModelIntegration extends AbstractPimCatalogIntegration
{
    const PRODUCT_MODEL_DOCUMENT_TYPE = 'pim_catalog_product_model';

    public function testDefaultDisplay()
    {
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /** @group todo */
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
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults(
            $query,
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, ['model-tshirt', 'model-tshirt-unique']);
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
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, ['model-tshirt-red', 'model-tshirt-unique']);
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
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
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
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, ['model-tshirt-blue', 'watch']);
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
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts(
            $productsFound,
            ['tshirt-grey-s', 'tshirt-blue-s', 'tshirt-red-s', 'tshirt-unique-s']
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
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts(
            $productsFound,
            [
                'tshirt-grey-m',
                'tshirt-blue-m',
                'tshirt-red-m',
                'tshirt-unique-m',
                'hat-m',
            ]
        );
    }

    /** @group todo */
    public function testSearchColorGreyAndSizeM()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'has_parent' => [
                                'type'  => 'pim_catalog_product_model_0',
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
            [self::PRODUCT_MODEL_DOCUMENT_TYPE . '_0', self::PRODUCT_MODEL_DOCUMENT_TYPE . '_1']
        );

        $this->assertProducts($productsFound, ['tshirt-grey-m', 'hat-m']);
    }

    // Do more complex use cases
    // - Having a configuration where variation goes inverse: size - color (for instance)
    // - Having a configuration where variation is diffenent: material - size (for instance)
    // - Where color == grey and name == tshirt (Search on a model and one property of his parent)

    /**
     * {@inheritdoc}
     */
    protected function addProducts()
    {
        $productModels = [
            // simple tshirt
            [
                'identifier' => 'model-tshirt',
                'level'      => 1,
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
                'identifier' => 'model-tshirt-grey',
                'parent'     => 'model-tshirt',
                'routing'    => 'model-tshirt',
                'level'      => 0,
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
                'identifier' => 'model-tshirt-blue',
                'parent'     => 'model-tshirt',
                'routing'    => 'model-tshirt',
                'level'      => 0,
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
                'identifier' => 'model-tshirt-red',
                'parent'     => 'model-tshirt',
                'routing'    => 'model-tshirt',
                'level'      => 0,
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
                'identifier' => 'model-tshirt-unique',
                'parent'     => null,
                'routing'    => 'model-tshirt-unique',
                'level'      => 0,
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
                'identifier' => 'model-hat',
                'parent'     => 'model-hat',
                'routing'    => 'model-hat',
                'level'      => 0,
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
                'identifier' => 'tshirt-grey-s',
                'parent'     => 'model-tshirt-grey',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-grey-m',
                'parent'     => 'model-tshirt-grey',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-grey-l',
                'parent'     => 'model-tshirt-grey',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-grey-xl',
                'parent'     => 'model-tshirt-grey',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-blue-s',
                'parent'     => 'model-tshirt-blue',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-blue-m',
                'parent'     => 'model-tshirt-blue',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-blue-l',
                'parent'     => 'model-tshirt-blue',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-blue-xl',
                'parent'     => 'model-tshirt-blue',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-red-s',
                'parent'     => 'model-tshirt-red',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-red-m',
                'parent'     => 'model-tshirt-red',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-red-l',
                'parent'     => 'model-tshirt-red',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-red-xl',
                'parent'     => 'model-tshirt-red',
                'routing'    => 'model-tshirt',
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
                'identifier' => 'tshirt-unique-s',
                'parent'     => 'model-tshirt-unique',
                'routing'    => 'model-tshirt-unique',
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
                'identifier' => 'tshirt-unique-m',
                'parent'     => 'model-tshirt-unique',
                'routing'    => 'model-tshirt-unique',
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
                'identifier' => 'tshirt-unique-l',
                'parent'     => 'model-tshirt-unique',
                'routing'    => 'model-tshirt-unique',
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
                'identifier' => 'tshirt-unique-xl',
                'parent'     => 'model-tshirt-unique',
                'routing'    => 'model-tshirt-unique',
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
                'parent'     => 'watch',
                'routing'    => 'watch',
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
                    'color-option'     => [
                        '<all_channels>' => [
                            '<all_locales>' => 'blue',
                        ],
                    ],
                ],
            ],

            // Hats variants (varying on size)
            [
                'identifier' => 'hat-m',
                'parent'     => 'model-hat',
                'routing'    => 'model-hat',
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
                'parent'     => 'model-hat',
                'routing'    => 'model-hat',
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

        $this->indexProductModels($productModels);
        $this->indexProducts($productVariants);
    }

    private function indexProductModels($productModels)
    {
        foreach ($productModels as $product) {
            $parentId = null;
            if (isset($product['parent'])) {
                $parentId = $product['parent'];
                unset($product['parent']);
            }

//            echo self::PRODUCT_MODEL_DOCUMENT_TYPE . '_' . $product['level'] . ' <- ' . $product['identifier'] . "\n";

            $this->esClient->index(
                self::PRODUCT_MODEL_DOCUMENT_TYPE . '_' . $product['level'],
                $product['identifier'],
                $parentId,
                $product
            );
        }

        $this->esClient->refreshIndex();
    }
}
