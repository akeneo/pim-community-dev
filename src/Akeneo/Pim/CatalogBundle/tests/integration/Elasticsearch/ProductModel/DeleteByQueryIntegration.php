<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\ProductModel;

use Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration\AbstractPimCatalogTestCase;

/**
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class DeleteByQueryIntegration extends AbstractPimCatalogTestCase
{
    /**
     * @test
     */
    public function it_deletes_by_query()
    {
        $this->get('akeneo_elasticsearch.client.product')->deleteByQuery([
            'query' => [
                'term' => [
                    'ancestors.ids' => 'product_model_1',
                ],
            ],
        ]);

        sleep(1); // delete_by_query is async

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
