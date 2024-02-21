<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PimCatalogFamilyVariantIntegration extends AbstractPimCatalogProductModel
{
    public function test_pqb_is_able_to_search_products_and_product_models_in_terms_of_a_list_of_family_variants()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'family_variant' => ['accessories_size', 'clothing_color'],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            [
                'model-hat',
                'hat-m',
                'hat-l',
                'model-tshirt-unique-size',
                'tshirt-unique-size-blue',
                'tshirt-unique-size-red',
                'tshirt-unique-size-yellow',
            ]
        );
    }

    public function test_pqb_is_able_to_search_products_and_product_models_which_are_not_in_a_list_of_family_variants()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'terms' => [
                            'family_variant' => [
                                'clothing_color_size',
                                'clothing_size',
                                'accessories_size',
                                'shoes_size_color',
                                'clothing_material_size',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            [
                'camera_nikon',
                'empty_product',
                'model-tshirt-unique-size',
                'tshirt-unique-size-blue',
                'tshirt-unique-size-red',
                'tshirt-unique-size-yellow',
                'watch',
            ]
        );
    }

    public function test_pqb_is_able_to_search_products_and_product_models_which_have_no_family_variant()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'family_variant',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, [
            'camera_nikon',
            'empty_product',
            'watch'
        ]);
    }

    public function test_pqb_is_able_to_search_products_and_product_models_which_have_a_family_variant()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => [
                            'field' => 'family_variant',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            [
                'model-tshirt',
                'model-tshirt-grey',
                'tshirt-grey-s',
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',
                'model-tshirt-blue',
                'tshirt-blue-s',
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',
                'model-tshirt-red',
                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',
                'model-tshirt-unique-color',
                'tshirt-unique-color-s',
                'tshirt-unique-color-m',
                'tshirt-unique-color-l',
                'tshirt-unique-color-xl',
                'model-hat',
                'hat-m',
                'hat-l',
                'model-tshirt-unique-size',
                'tshirt-unique-size-blue',
                'tshirt-unique-size-red',
                'tshirt-unique-size-yellow',
                'model-running-shoes',
                'model-running-shoes-s',
                'running-shoes-s-white',
                'running-shoes-s-blue',
                'running-shoes-s-red',
                'model-running-shoes-m',
                'running-shoes-m-white',
                'running-shoes-m-blue',
                'running-shoes-m-red',
                'model-running-shoes-l',
                'running-shoes-l-white',
                'running-shoes-l-blue',
                'running-shoes-l-red',
                'model-biker-jacket',
                'model-biker-jacket-leather',
                'biker-jacket-leather-s',
                'biker-jacket-leather-m',
                'biker-jacket-leather-l',
                'model-biker-jacket-polyester',
                'biker-jacket-polyester-s',
                'biker-jacket-polyester-m',
                'biker-jacket-polyester-l',
            ]
        );
    }
}
