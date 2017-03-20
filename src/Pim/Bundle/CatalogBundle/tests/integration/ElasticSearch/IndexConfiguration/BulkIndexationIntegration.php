<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

class BulkIndexationIntegration extends AbstractPimCatalogIntegration
{
    public function testIndexationOnABulk()
    {
        $count = 5;
        $products = [];
        for ($i = 1; $i <= $count; $i++) {
            $products[] = ['identifier' => 'product_' . $i];
        }

        $indexedProducts = $this->esClient->bulkIndexes(self::DOCUMENT_TYPE, $products, 'identifier');
        $this->assertFalse($indexedProducts['errors']);
        $this->assertCount($count, $indexedProducts['items']);

        foreach ($indexedProducts['items'] as $index => $indexedProduct) {
            $this->assertSame('product_' . $index+=1, $indexedProduct['index']['_id']);

            $result = 'product_1' === $indexedProduct['index']['_id'] ? 'updated' : 'created';
            $version = 'product_1' === $indexedProduct['index']['_id'] ? 2 : 1;
            $this->assertSame($result, $indexedProduct['index']['result']);
            $this->assertSame($version, $indexedProduct['index']['_version']);
        }
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    protected function addProducts()
    {
        $products = [
            [
                'identifier'       => 'product_1',
                'description-text' => 'My product description',
            ]
        ];

        $this->indexProducts($products);
    }
}
