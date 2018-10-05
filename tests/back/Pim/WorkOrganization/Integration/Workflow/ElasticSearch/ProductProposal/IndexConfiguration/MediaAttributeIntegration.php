<?php

namespace AkeneoTest\Pim\WorkOrganization\Integration\Workflow\ElasticSearch\ProductProposal\IndexConfiguration;

class MediaAttributeIntegration extends AbstractProductProposalTestCase
{
    public function testStartWithOperator()
    {
        $query = $this->buildQuery([
            [
                'query_string' => [
                    'default_field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                    'query'         => 'yet*',
                ],
            ],
            [
                'query_string' => [
                    'default_field' => 'values.an_image-media.ecommerce.fr_FR.original_filename',
                    'query'         => 'yet*',
                ],
            ]
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_3', 'product_6']);
    }

    public function testContainsOperator()
    {
        $query = $this->buildQuery([
            [
                'query_string' => [
                    'default_field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                    'query'         => '*jpeg*',
                ],
            ],
        ]);

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_2', 'product_3']);
    }

    public function testDoesNotContainOperator()
    {
        $query = $this->buildQuery(
            [
                [
                    'exists' => [
                        'field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                    ]
                ],
            ],
            [
                [
                    'query_string' => [
                        'default_field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                        'query'         => '*another*',
                    ],
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_1', 'product_4', 'product_7']);
    }

    public function testEqualsOperator()
    {
        $query = $this->buildQuery(
            [
                [
                    'term' => [
                        'values.an_image-media.ecommerce.en_US.original_filename' => 'i_have_no_imagination.jpg'
                    ],
                ],
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_7']);
    }

    public function testDoesNotEqualOperator()
    {
        $query = $this->buildQuery(
            [
                [
                    'exists' => [
                        'field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                    ]
                ],
            ],
            [
                [
                    'query_string' => [
                        'default_field' => 'values.an_image-media.ecommerce.en_US.original_filename',
                        'query'         => 'i_have_no_imagination.jpg',
                    ],
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument(
            $productsFound,
            ['product_1', 'product_2', 'product_3', 'product_4', 'product_5', 'product_6']
        );
    }

    public function testEmptyOperator()
    {
        $query = $this->buildQuery(
            [],
            [
                [
                    'exists' => ['field' => 'values.an_image-media.ecommerce.en_US']
                ]
            ]
        );

        $productsFound = $this->getSearchQueryResults($query);

        $this->assertDocument($productsFound, ['product_8']);
    }

    public function testIsNotEmptyOperator()
    {
        $query = $this->buildQuery(
            [
                [
                    'exists' => ['field' => 'values.an_image-media.ecommerce.en_US']
                ]
            ]
        );

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
                        'ecommerce' => [
                            'fr_FR' => [
                                'extension'         => 'jpg',
                                'hash'              => 'a_hash',
                                'key'               => 'my/relative/path/to_a_jpeg_image.jpg',
                                'mime_type'         => 'image/jpeg',
                                'original_filename' => 'french_image.jpg',
                                'size'              => 42,
                                'storage'           => 'catalogStorage',
                            ],
                            'en_US' => [
                                'extension'         => 'jpg',
                                'hash'              => 'a_hash',
                                'key'               => 'my/relative/path/to_a_jpeg_image.jpg',
                                'mime_type'         => 'image/jpeg',
                                'original_filename' => 'english_image.jpg',
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
                        'ecommerce' => [
                            'en_US' => [
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
                        'ecommerce' => [
                            'en_US' => [
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
                        'ecommerce' => [
                            'en_US' => [
                                'extension'         => 'png',
                                'hash'              => 'a_hash',
                                'key'               => 'my/relative/path/to_a_png_image.png',
                                'mime_type'         => 'image/png',
                                'original_filename' => 'a_png_image.png',
                                'size'              => 42,
                                'storage'           => 'catalogStorage',
                            ],
                            'fr_FR' => [
                                'extension'         => 'png',
                                'hash'              => 'a_hash',
                                'key'               => 'my/relative/path/to_a_png_image.png',
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
                'identifier' => 'product_5',
                'values'     => [
                    'an_image-media' => [
                        'ecommerce' => [
                            'en_US' => [
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
                        'ecommerce' => [
                            'en_US' => [
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
                        'ecommerce' => [
                            'en_US' => [
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
