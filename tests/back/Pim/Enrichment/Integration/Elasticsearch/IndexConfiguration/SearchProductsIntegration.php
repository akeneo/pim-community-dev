<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * Search use cases of products only (no product models should be returned).
 *
 * The search is performed independently from the family variants.
 *
 * The search takes advantage of the following properties to elaborate concise but powerful requests that retrieve
 * only products and product variants.
 *
 * - Each document (e.g: products, product variants or models) has all the properties of its associated parent model
 *   and grand parent models.
 *
 * - Each document has a property 'document_type' which gives an hint about the level in the family variant the document
 *   belongs to.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchProductsIntegration extends AbstractPimCatalogProductModel
{
    public function testDefaultProductExport()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        [
                            'terms' => ['document_type' => [ProductInterface::class]],
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
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',

                'tshirt-blue-s',
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',

                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',

                'tshirt-unique-color-s',
                'tshirt-unique-color-m',
                'tshirt-unique-color-l',
                'tshirt-unique-color-xl',

                'watch',

                'hat-m',
                'hat-l',

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

                'biker-jacket-leather-s',
                'biker-jacket-leather-m',
                'biker-jacket-leather-l',

                'biker-jacket-polyester-s',
                'biker-jacket-polyester-m',
                'biker-jacket-polyester-l',

                'empty_product',
                'camera_nikon',
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
                            'terms' => ['document_type' => [ProductInterface::class]],
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
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',

                'tshirt-blue-s',
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',

                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',

                'tshirt-unique-color-s',
                'tshirt-unique-color-m',
                'tshirt-unique-color-l',
                'tshirt-unique-color-xl',

                'tshirt-unique-size-blue',
                'tshirt-unique-size-red',
                'tshirt-unique-size-yellow',
            ]
        );
    }

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
                            'terms' => ['document_type' => [ProductInterface::class]],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            [
                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',

                'tshirt-unique-color-s',
                'tshirt-unique-color-m',
                'tshirt-unique-color-l',
                'tshirt-unique-color-xl',

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
                            'terms' => ['document_type' => [ProductInterface::class]],
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
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',

                'hat-m',
                'hat-l',
            ]);
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
                            'terms' => ['document_type' => [ProductInterface::class]],
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
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',

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
                            'terms' => ['document_type' => [ProductInterface::class]],
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

                'running-shoes-s-white',
                'running-shoes-s-blue',
                'running-shoes-s-red',

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
                            'terms' => ['document_type' => [ProductInterface::class]],
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

                'running-shoes-m-white',
                'running-shoes-m-blue',
                'running-shoes-m-red',

                'biker-jacket-leather-m',
                'biker-jacket-polyester-m',
            ]
        );
    }

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
                            'terms' => ['document_type' => [ProductInterface::class]],
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
                            'terms' => ['document_type' => [ProductInterface::class]],
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
                            'terms' => ['document_type' => [ProductInterface::class]],
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
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',
            ]
        );
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
                            'terms' => ['document_type' => [ProductInterface::class]],
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
                'tshirt-grey-m',
                'tshirt-grey-l',
                'tshirt-grey-xl',

                'tshirt-blue-s',
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',

                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',

                'tshirt-unique-color-s',
                'tshirt-unique-color-m',
                'tshirt-unique-color-l',
                'tshirt-unique-color-xl',

                'tshirt-unique-size-blue',
                'tshirt-unique-size-red',
                'tshirt-unique-size-yellow',
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
                            'terms' => ['document_type' => [ProductInterface::class]],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            [
                'running-shoes-s-white',
                'running-shoes-s-blue',
                'running-shoes-s-red',

                'running-shoes-m-white',
                'running-shoes-m-blue',
                'running-shoes-m-red',

                'running-shoes-l-white',
                'running-shoes-l-blue',
                'running-shoes-l-red',

                'biker-jacket-leather-s',
                'biker-jacket-leather-m',
                'biker-jacket-leather-l',
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
                            'terms' => ['document_type' => [ProductInterface::class]],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound,
            [
                'running-shoes-m-white',
                'biker-jacket-polyester-m',
                'biker-jacket-leather-m'
            ]);
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
                            'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
                        ],
                        [
                            'terms' => ['document_type' => [ProductInterface::class]],
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
                'tshirt-blue-m',
                'tshirt-blue-l',
                'tshirt-blue-xl',

                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',

                'tshirt-unique-color-s',
                'tshirt-unique-color-m',
                'tshirt-unique-color-l',
                'tshirt-unique-color-xl',

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

                'biker-jacket-leather-s',
                'biker-jacket-leather-m',
                'biker-jacket-leather-l',

                'biker-jacket-polyester-s',
                'biker-jacket-polyester-m',
                'biker-jacket-polyester-l',
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
                            'terms' => ['document_type' => [ProductInterface::class]],
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            [
                'tshirt-red-s',
                'tshirt-red-m',
                'tshirt-red-l',
                'tshirt-red-xl',

                'tshirt-unique-color-s',
                'tshirt-unique-color-m',
                'tshirt-unique-color-l',
                'tshirt-unique-color-xl',

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
                            'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
                        ],
                        [
                            'terms' => ['document_type' => [ProductInterface::class]],
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
                            'exists' => ['field' => 'values.color-option.<all_channels>.<all_locales>'],
                        ],
                        [
                            'exists' => ['field' => 'values.size-option.<all_channels>.<all_locales>'],
                        ],
                        [
                            'terms' => ['document_type' => [ProductInterface::class]],
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
