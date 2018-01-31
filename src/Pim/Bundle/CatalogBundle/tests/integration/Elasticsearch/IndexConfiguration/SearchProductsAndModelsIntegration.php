<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

use Pim\Component\Catalog\Model\ProductInterface;
use Pim\Component\Catalog\Model\ProductModelInterface;

/**
 * Search use cases of products and models in a "smart datagrid way".
 * It returns either products or models depending on where the information is stored.
 *
 * The search is performed independently from the family variants.
 *
 * The search takes advantage of the following properties to elaborate concise but powerful requests:
 *
 * - Each document (e.g: products, product variants or models) has all the properties of its associated parent model
 *   and grand parent models.
 *
 * - Each documents has an 'attributes_for_this_level' property which is a list of the attribute codes that belong to the
 *   document (following the family variant settings and levels definition).
 *
 * - Each document has a property 'document_type' which gives an hint about the level in the family variant the document
 *   belongs to.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchProductsAndModelsIntegration extends AbstractPimCatalogProductModelIntegration
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

        $productsFound = $this->getSearchQueryResults(
            $query,
            [
                AbstractPimCatalogProductModelIntegration::DOCUMENT_TYPE,
            ]
        );

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
            ]
        );
    }

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
                        [
                            'terms' => ['attributes_for_this_level' => ['description']],
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
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['color']],
                        ],
                    ],
                ],
            ],
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
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['color']],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['model-tshirt-grey', 'model-hat']);
    }

    public function testSearchColorBlue()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['blue']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['color']],
                        ],
                    ],
                ],
            ],
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
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['size']],
                        ],
                    ],
                ],
            ],
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
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['m']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['size']],
                        ],
                    ],
                ],
            ],
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
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['color', 'size']],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['tshirt-grey-s']);
    }

    public function testSearchColorGreyAndSizeM()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['m']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['color', 'size']],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['tshirt-grey-m', 'hat-m']);
    }

    public function testSearchColorGreyAndDescriptionTshirt()
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
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['color', 'description']],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['model-tshirt-grey']);
    }

    public function testSearchMaterialCotton()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['cotton']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['material']],
                        ],
                    ],
                ],
            ],
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
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['leather']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['material']],
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
                'model-biker-jacket-leather',
            ]
        );
    }

    public function testSearchSizeMAndGrandParentColorWhite()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['m']],
                        ],
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['white']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['size', 'color']],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound,
            ['running-shoes-m-white', 'biker-jacket-polyester-m', 'biker-jacket-leather-m']);
    }

    public function testNotGrey()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                    ],
                    'filter'   => [
                        [
                            'terms' => ['attributes_for_this_level' => ['color']],
                        ],
                        [
                            'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
                        ],
                    ],
                ],
            ],
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
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['values.material-option.<all_channels>.<all_locales>' => ['cotton']],
                        ],
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['red']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['color', 'material']],
                        ],
                    ],
                ],
            ],
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
                'bool' => [
                    'must_not' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                    ],
                    'filter'   => [
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                        ],
                        [
                            'terms' => ['attributes_for_this_level' => ['color', 'size']],
                        ],
                        [
                            'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
                        ],
                    ],
                ],
            ],
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
                'bool' => [
                    'must_not' => [
                        [
                            'terms' => ['values.color-option.<all_channels>.<all_locales>' => ['grey']],
                        ],
                        [
                            'terms' => ['values.size-option.<all_channels>.<all_locales>' => ['s']],
                        ],
                    ],
                    'filter'   => [
                        [
                            'terms' => ['attributes_for_this_level' => ['color', 'size']],
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
}
