<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

/**
 * Search use cases of products and product models in a "smart way".
 * It returns either products or product models depending on which entity is holding the information we are trying to
 * filter on.
 *
 * The search is performed independently from the family variants.
 *
 * The search takes advantage of the following properties to elaborate concise but powerful requests:
 *
 * - Each document (e.g: products, product variants or product models) has all the properties of its associated
 * ancestors product models.
 *
 * - Each documents has an 'attribute_of_ancestors' property which is a list of the attribute codes that belong to the
 * product model ancestors (ie, all attribute codes of the parents and grand parent product models).
 *
 * - Each document has a property 'document_type' which gives an hint about the level in the family variant the document
 *   belongs to.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchProductsAndModelsIntegration extends AbstractPimCatalogProductModel
{
    /**
     * Default display is: search for the root product models and products".
     */
    public function testDefaultDisplay()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => [
                            'field' => 'parent',
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
                'model-tshirt-unique-color',
                'watch',
                'model-hat',
                'model-tshirt-unique-size',
                'model-running-shoes',
                'model-biker-jacket',
                'empty_product',
                'camera_nikon'
            ]
        );
    }

    public function testSearchTshirtInDescription()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'query_string' => [
                                        'default_field' => 'values.description-text.<all_channels>.<all_locales>',
                                        'query'         => '*T-shirt*',
                                    ],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['description']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            [
                'model-tshirt',
                'model-tshirt-unique-color',
                'model-tshirt-unique-size',
            ]
        );
    }

    /**
     * Simple search request that will return a mixed results of:
     * - VariantProducts (running-shoes-*)
     * - SubProductModel (model-tshirt-red)
     * - RootProductModel (model-tshirt-unique-color)
     *
     * This mixed result is explained by the fact that the attribute "color" is not set at the same level within those 3
     * family variants.
     */
    public function testSearchColorRed()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
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
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['model-tshirt-grey', 'model-hat']);
    }

    public function testSearchColorBlue()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['blue']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
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
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['size']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
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
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['m']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['size']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
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

    public function testSearchColorIsEmpty()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'must_not' => [
                                'exists' => [
                                    'field' => 'values.color-option.<all_channels>.<all_locales>'
                                ]
                            ],
                            'filter' => [
                                [
                                    'terms' => [
                                        'attributes_for_this_level' => ['color'],
                                    ],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            [
                'camera_nikon'
            ]
        );
    }

    public function testSearchBrandIsEmpty()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'must_not' => [
                                [
                                    'exists' => [
                                        'field' => 'values.brand-option.<all_channels>.<all_locales>',
                                    ],
                                ],
                            ],
                            'filter' => [
                                [
                                    'terms' => ['attributes_for_this_level' => ['brand']]
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['brand']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            [
                'empty_product'
            ]
        );
    }

    public function testSearchBrandIsNotEmpty()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'exists' => [
                                        'field' => 'values.brand-option.<all_channels>.<all_locales>',
                                    ],
                                ],
                                [
                                    'terms' => ['attributes_for_this_level' => ['brand']]
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['brand']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            [
                'camera_nikon'
            ]
        );
    }

    /**
     * Search request with 2 different attributes.
     *
     * Given those 2 attributes and a family variant (tree),
     * the search should return the documents which:
     * - level is the lowest between the levels set of those attributes
     * - and satisfy both conditions of the search.
     *
     * Ex: when searching for color=grey and size=s,
     *
     * We can see that the only products that satisfy those conditions are:
     * - The tshirt products and tshirt models with color grey and size s
     * - In the "clothing_color_size" family variant, size is defined at the product leve while color is defined at the
     *   subProductModel level. So we show the documents that belongs to the lowest involved. here level products.
     */
    public function testSearchColorGreyAndSizeS()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                                ],
                                [
                                    'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['size']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['tshirt-grey-s']);
    }

    public function testSearchColorGreyAndSizeM()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                                ],
                                [
                                    'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['m']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['size']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];


        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['tshirt-grey-m', 'hat-m']);
    }

    public function testSearchColorGreyAndDescriptionTshirt()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
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
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['description']],
                                                    ],
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['model-tshirt-grey']);
    }

    public function testSearchMaterialCotton()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['cotton']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['material']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
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
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['leather']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['material']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
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
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['m']],
                                ],
                                [
                                    'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['white']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['size']],
                                                    ],
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound,
            ['running-shoes-m-white', 'biker-jacket-polyester-m', 'biker-jacket-leather-m']);
    }

    public function testNotGrey()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'must_not' => [
                                [
                                    'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                                ],
                            ],
                            'filter' => [
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];


        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
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
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                [
                                    'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['cotton']],
                                ],
                                [
                                    'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['material']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
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
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'must_not' => [
                                [
                                    'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                                ],
                            ],
                            'filter' => [
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['size']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
                                ],
                                [
                                    'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
                                ],
                                [
                                    'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
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
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'must_not' => [
                                [
                                    'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                                ],
                                [
                                    'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                                ],
                            ],
                            'filter' => [
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['size']],
                                                    ],
                                                ],
                                            ],
                                        ],
                                    ],
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
                ],
            ]
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
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

    public function testCategoryShoes()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter'   => [
                                [
                                    'terms' => [
                                        'categories' => ['shoes']
                                    ]
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['categories_of_ancestors' => ['shoes']]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
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
                'model-running-shoes',
            ]
        );
    }

    public function testCategoryShoesAndSizeS()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter'   => [
                                [
                                    'terms' => ['categories' => ['shoes']],
                                ],
                                [
                                    'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['size']],
                                                    ],
                                                    [
                                                        'terms' => ['categories_of_ancestors' => ['shoes']]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
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
                'model-running-shoes-s',
            ]
        );
    }

    public function testCategoryShoesAndColorWhite()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter'   => [
                                [
                                    'terms' => ['categories' => ['shoes']],
                                ],
                                [
                                    'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['white']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['color']],
                                                    ],
                                                    [
                                                        'terms' => ['categories_of_ancestors' => ['shoes']]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
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
                'running-shoes-l-white',
                'running-shoes-m-white',
                'running-shoes-s-white'
            ]
        );
    }

    public function testCategoryMen()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter'   => [
                                [
                                    'terms' => ['categories' => ['men']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['categories_of_ancestors' => ['men']]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
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
                'running-shoes-l-blue',
                'running-shoes-l-white',
                'running-shoes-m-blue',
                'running-shoes-m-white',
                'running-shoes-s-blue',
                'running-shoes-s-white',
            ]
        );
    }

    public function testCategoryMenAndMaterialLeather()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter'   => [
                                [
                                    'terms' => ['categories' => ['men']],
                                ],
                                [
                                    'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['leather']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['categories_of_ancestors' => ['men']]
                                                    ],
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['material']]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
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
                'running-shoes-l-blue',
                'running-shoes-l-white',
                'running-shoes-m-blue',
                'running-shoes-m-white',
                'running-shoes-s-blue',
                'running-shoes-s-white',
            ]
        );
    }

    public function testCategoryFootwearAndSizeS()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter'   => [
                                [
                                    'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                                ],
                                [
                                    'terms' => ['categories' => ['shoes']],
                                ],
                                [
                                    'bool' => [
                                        'must_not' => [
                                            'bool' => [
                                                'filter' => [
                                                    [
                                                        'terms' => ['attributes_of_ancestors' => ['size']]
                                                    ],
                                                    [
                                                        'terms' => ['categories_of_ancestors' => ['shoes']]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
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
                'model-running-shoes-s'
            ]
        );
    }
}
