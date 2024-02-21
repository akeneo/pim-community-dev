<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DocumentTypeIntegration extends AbstractPimCatalogTestCase
{
    /**
     * @test
     */
    public function it_tests_the_equals_operator_on_product_models()
    {
        $documentType = str_replace('\\', '\\\\', ProductModelInterface::class);
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'query_string' => [
                                    'default_field' => 'document_type',
                                    'query'         => $documentType,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $documentsFound = $this->getSearchQueryResults($query);
        $this->assertDocument(
            $documentsFound,
            ['model-tshirt', 'model-tshirt-blue', 'model-tshirt-white', 'model-tshirt-red']
        );
    }

    /**
     * @test
     */
    public function it_tests_the_equals_operator_on_products()
    {
        $documentType = str_replace('\\', '\\\\', ProductInterface::class);
        $query = [
            'query' => [
                'constant_score' => [
                    'filter' => [
                        'bool' => [
                            'filter' => [
                                'query_string' => [
                                    'default_field' => 'document_type',
                                    'query'         => $documentType,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];
        $documentsFound = $this->getSearchQueryResults($query);
        $this->assertDocument(
            $documentsFound,
            [
                'model-tshirt-white-s',
                'model-tshirt-white-m',
                'model-tshirt-white-l',
                'model-tshirt-red-s',
                'model-tshirt-red-m',
                'model-tshirt-red-l',
                'model-tshirt-blue-s',
                'model-tshirt-blue-m',
                'model-tshirt-blue-l',
            ]
        );
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    protected function addDocuments()
    {
        $products = [
            [
                'identifier' => 'model-tshirt',
                'document_type' => ProductModelInterface::class
            ],
            [
                'identifier' => 'model-tshirt-white',
                'document_type' => ProductModelInterface::class
            ],
            [
                'identifier' => 'model-tshirt-blue',
                'document_type' => ProductModelInterface::class
            ],
            [
                'identifier' => 'model-tshirt-red',
                'document_type' => ProductModelInterface::class
            ],
            [
                'identifier' => 'model-tshirt-white-s',
                'document_type' => ProductInterface::class
            ],
            [
                'identifier' => 'model-tshirt-white-m',
                'document_type' => ProductInterface::class
            ],
            [
                'identifier' => 'model-tshirt-white-l',
                'document_type' => ProductInterface::class
            ],
            [
                'identifier' => 'model-tshirt-red-s',
                'document_type' => ProductInterface::class
            ],
            [
                'identifier' => 'model-tshirt-red-m',
                'document_type' => ProductInterface::class
            ],
            [
                'identifier' => 'model-tshirt-red-l',
                'document_type' => ProductInterface::class
            ],
            [
                'identifier' => 'model-tshirt-blue-s',
                'document_type' => ProductInterface::class
            ],
            [
                'identifier' => 'model-tshirt-blue-m',
                'document_type' => ProductInterface::class
            ],
            [
                'identifier' => 'model-tshirt-blue-l',
                'document_type' => ProductInterface::class
            ],
        ];

        $this->indexDocuments($products);
    }
}
