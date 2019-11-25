<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\ProductModel;

use AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration\AbstractPimCatalogTestCase;

/**
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteByQueryIntegration extends AbstractPimCatalogTestCase
{
    public function testItDeletesByQuery()
    {
        $this->get('akeneo_elasticsearch.client.product_and_product_model')->deleteByQuery([
            'query' => [
                'term' => [
                    'ancestors.ids' => 'product_model_1',
                ],
            ],
        ]);

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

        $productsFound = $this->getSearchQueryResults([
            'query' => [
                'bool' => [
                    'filter' => [
                        'terms' => [
                            'identifier' => ['product_model_2', 'variant-1', 'variant-2'],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertSame($productsFound, []);
    }

    /**
     * {@inheritdoc}
     */
    protected function addDocuments()
    {
        $products = [
            [
                'identifier' => 'variant-1',
                'ancestors.ids' => ['product_model_1', 'product_model_2'],
            ],
            [
                'identifier' => 'variant-2',
                'ancestors.ids' => ['product_model_1', 'product_model_2'],
            ],
            [
                'identifier' => 'product_model_2',
                'ancestors.ids' => ['product_model_1'],
            ],
            [
                'identifier' => 'product_model_1',
                'ancestors.ids' => [],
            ],
        ];

        $this->indexDocuments($products);
    }
}
