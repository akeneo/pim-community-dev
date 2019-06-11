<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

/**
 * Search use cases of products and product models using the "ancestors" field.
 *
 * @author    Samir Boulil <samir.boulil@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class SearchSelfAndAncestorsIntegration extends AbstractPimCatalogProductModel
{
    /**
     * Find all products and products models with Id + documents which are ancestors of this document
     * (a root product model with two levels)
     */
    public function testFindAllProductsAndProductModelsWithIdAndAncestorIdsOf()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'id' => ['product_model_8'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.ids' => ['product_model_8'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
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
            ]
        );
    }

    /**
     * Find all products and products models with Id + documents which are ancestors of this document
     * (a root product model with one level)
     */
    public function testFindAllProductsAndProductModelsWithIdAndAncestorIdsOf1()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'id' => ['product_model_6'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.ids' => ['product_model_6'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'model-hat',
                'hat-m',
                'hat-l',
            ]
        );
    }

    /**
     * Find all products and products models with Id + documents which are ancestors of this document
     * (a sub product model)
     */
    public function testFindAllProductsAndProductModelsWithIdAndAncestorIdsOf2()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'id' => ['product_model_10'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.ids' => ['product_model_10'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'model-running-shoes-m',
                'running-shoes-m-white',
                'running-shoes-m-blue',
                'running-shoes-m-red',
            ]
        );
    }

    /**
     * Find all products and products models with Id + documents which have for ancestors the document with ids
     * (multiple product models and a product) model and a product model id)
     */
    public function testFindAllProductsAndProductModelsWithIdAndAncestorIdsOf3()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'id' => ['product_model_10', 'product_17', 'product_model_5'],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'ancestors.ids' => ['product_model_10', 'product_17', 'product_model_5'],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'model-running-shoes-m',
                'running-shoes-m-white',
                'running-shoes-m-blue',
                'running-shoes-m-red',
                'watch',
                'model-tshirt-unique-color',
                'tshirt-unique-color-s',
                'tshirt-unique-color-m',
                'tshirt-unique-color-l',
                'tshirt-unique-color-xl',
            ]
        );
    }

    public function testFindAllProductsAndProductModelsWhichDoesNotHaveIdAndAreNotAncestorsOf()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'must_not' => [
                                [
                                    'terms' => [
                                        'ancestors.ids' => [
                                            'product_model_1',
                                            'product_model_5',
                                            'product_model_6',
                                            'product_model_7',
                                            'product_model_8',
                                        ],
                                    ],
                                ],
                                [
                                    'terms' => [
                                        'id' => [
                                            'product_model_1',
                                            'product_model_5',
                                            'product_model_6',
                                            'product_model_7',
                                            'product_model_8',
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $productsAndProductModelsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsAndProductModelsFound,
            [
                'model-biker-jacket',
                'biker-jacket-leather-s',
                'biker-jacket-leather-m',
                'biker-jacket-leather-l',
                'biker-jacket-polyester-s',
                'biker-jacket-polyester-m',
                'biker-jacket-polyester-l',
                'camera_nikon',
                'model-biker-jacket-leather',
                'model-biker-jacket-polyester',
                'watch',
                'empty_product'
            ]
        );
    }

    /**
     * Find all products which are ancestors of this document, using the code
     */
    public function testFindAllProductsAndProductModelsWithIdAndAncestorCodesOf()
    {
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'should' => [
                                [
                                    'terms' => [
                                        'ancestors.codes' => ['model-running-shoes-l'],
                                    ],
                                ],
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
                'running-shoes-l-red',
                'running-shoes-l-white'
            ]
        );
    }
}
