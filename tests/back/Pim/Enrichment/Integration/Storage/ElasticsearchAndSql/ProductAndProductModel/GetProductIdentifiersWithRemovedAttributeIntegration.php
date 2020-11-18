<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductIdentifiersWithRemovedAttributeInterface;

class GetProductIdentifiersWithRemovedAttributeIntegration extends WithRemovedAttributeTestCase
{
    public function test_it_retrieves_product_identifiers_with_a_removed_attribute()
    {
        $this->removeAttribute('an_attribute');
        $this->removeAttribute('a_third_attribute');

        $result = $this->getProductIdentifiersWithRemovedAttribute()->nextBatch(['an_attribute', 'a_third_attribute'], 10);

        $batchCount = 0;
        foreach ($result as $batch) {
            $batchCount++;
            self::assertEquals(['product_1', 'product_2', 'product_3'], $batch);
        }
        self::assertEquals(1, $batchCount);
    }

    private function getProductIdentifiersWithRemovedAttribute(): GetProductIdentifiersWithRemovedAttributeInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_identifiers_with_removed_attribute');
    }
}
