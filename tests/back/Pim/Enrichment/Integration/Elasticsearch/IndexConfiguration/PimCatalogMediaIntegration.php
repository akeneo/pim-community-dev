<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class PimCatalogMediaIntegration extends AbstractPimCatalogTestCase
{
    public function testStartWithOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'values.an_image-media.<all_channels>.<all_locales>.original_filename',
                            'query'         => 'yet*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_6']);
    }

    public function testContainsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'query_string' => [
                            'default_field' => 'values.an_image-media.<all_channels>.<all_locales>.original_filename',
                            'query'         => '*jpeg*',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_2', 'product_3']);
    }

    public function testDoesNotContainOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'query_string' => [
                            'default_field' => 'values.an_image-media.<all_channels>.<all_locales>.original_filename',
                            'query'         => '*another*',
                        ],
                    ],
                    'filter' => [
                        'exists' => [
                            'field' => 'values.an_image-media.<all_channels>.<all_locales>',
                        ]
                    ]
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_4', 'product_7']);
    }

    public function testEqualsOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'term' => [
                            'values.an_image-media.<all_channels>.<all_locales>.original_filename' => 'i_have_no_imagination.jpg',
                        ],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_7']);
    }

    public function testDoesNotEqualOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'term' => [
                            'values.an_image-media.<all_channels>.<all_locales>.original_filename' => 'i_have_no_imagination.jpg',
                        ],
                    ],
                    'filter'   => [
                        'exists' => ['field' => 'values.an_image-media.<all_channels>.<all_locales>'],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testEmptyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'must_not' => [
                        'exists' => ['field' => 'values.an_image-media.<all_channels>.<all_locales>'],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_8']);
    }

    public function testIsNotEmptyOperator()
    {
        $query = [
            'query' => [
                'bool' => [
                    'filter' => [
                        'exists' => ['field' => 'values.an_image-media.<all_channels>.<all_locales>'],
                    ],
                ],
            ],
        ];

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6', 'product_7']
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function addDocuments()
    {
        $products = [
            [
                'identifier' => 'product_1',
                'values'     => [
                    'an_image-media' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension'         => 'jpg',
                                'hash'              => 'a_hash',
                                'key'               => 'my/relative/path/to_a_jpeg_image.jpg',
                                'mime_type'         => 'image/jpeg',
                                'original_filename' => 'a_jpeg_image.jpg',
                                'size'              => 42,
                                'storage'           => 'catalogStorage',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_2',
                'values'     => [
                    'an_image-media' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension'         => 'jpg',
                                'hash'              => 'a_hash',
                                'key'               => 'my/relative/path/to_another_jpeg_image.jpg',
                                'mime_type'         => 'image/jpeg',
                                'original_filename' => 'another_jpeg_image.jpg',
                                'size'              => 42,
                                'storage'           => 'catalogStorage',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_3',
                'values'     => [
                    'an_image-media' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension'         => 'jpg',
                                'hash'              => 'a_hash',
                                'key'               => 'my/relative/path/to_yet_another_jpeg_image.jpg',
                                'mime_type'         => 'image/jpeg',
                                'original_filename' => 'yet_another_jpeg_image.jpg',
                                'size'              => 42,
                                'storage'           => 'catalogStorage',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_4',
                'values'     => [
                    'an_image-media' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension'         => 'png',
                                'hash'              => 'a_hash',
                                'key'               => 'my/relative/path/to_a_png_image.png',
                                'mime_type'         => 'image/png',
                                'original_filename' => 'a_png_image.png',
                                'size'              => 42,
                                'storage'           => 'catalogStorage',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_5',
                'values'     => [
                    'an_image-media' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension'         => 'png',
                                'hash'              => 'a_hash',
                                'key'               => 'my/relative/path/to_another_png_image.png',
                                'mime_type'         => 'image/png',
                                'original_filename' => 'another_png_image.png',
                                'size'              => 42,
                                'storage'           => 'catalogStorage',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_6',
                'values'     => [
                    'an_image-media' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension'         => 'png',
                                'hash'              => 'a_hash',
                                'key'               => 'my/relative/path/to_yet_another_png_image.png',
                                'mime_type'         => 'image/png',
                                'original_filename' => 'yet_another_png_image.png',
                                'size'              => 42,
                                'storage'           => 'catalogStorage',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_7',
                'values'     => [
                    'an_image-media' => [
                        '<all_channels>' => [
                            '<all_locales>' => [
                                'extension'         => 'jpg',
                                'hash'              => 'a_hash',
                                'key'               => 'my/relative/path/to_i_have_no_imagination.jpg',
                                'mime_type'         => 'image/jpeg',
                                'original_filename' => 'i_have_no_imagination.jpg',
                                'size'              => 42,
                                'storage'           => 'catalogStorage',
                            ],
                        ],
                    ],
                ],
            ],
            [
                'identifier' => 'product_8',
            ],
        ];

        $this->indexDocuments($products);
    }
}
