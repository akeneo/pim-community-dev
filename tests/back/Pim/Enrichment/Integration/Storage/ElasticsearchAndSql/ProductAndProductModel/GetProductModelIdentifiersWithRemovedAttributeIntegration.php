<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\GetProductModelIdentifiersWithRemovedAttributeInterface;

class GetProductModelIdentifiersWithRemovedAttributeIntegration extends WithRemovedAttributeTestCase
{
    public function test_it_retrieves_product_identifiers_with_a_removed_attribute()
    {
        $this->removeAttribute('an_attribute');
        $this->removeAttribute('a_third_attribute');

        $result = $this->getProductModelIdentifiersWithRemovedAttribute()->nextBatch(['an_attribute', 'a_third_attribute'], 10);

        $batchCount = 0;
        foreach ($result as $batch) {
            $batchCount++;
            self::assertEquals(['a_product_model', 'a_sub_product_model'], $batch);
        }
        self::assertEquals(1, $batchCount);
    }

    private function getProductModelIdentifiersWithRemovedAttribute(): GetProductModelIdentifiersWithRemovedAttributeInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.get_product_model_identifiers_with_removed_attribute');
    }
}
