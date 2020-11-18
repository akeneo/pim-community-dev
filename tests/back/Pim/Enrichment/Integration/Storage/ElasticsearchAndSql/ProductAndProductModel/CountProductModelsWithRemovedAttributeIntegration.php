<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductModelsWithRemovedAttributeInterface;

class CountProductModelsWithRemovedAttributeIntegration extends WithRemovedAttributeTestCase
{
    public function test_it_only_counts_product_models_with_a_removed_attribute()
    {
        $this->removeAttribute('an_attribute');
        $this->removeAttribute('a_third_attribute');

        $count = $this->getCountProductModelsWithRemovedAttribute()->count(['an_attribute', 'a_third_attribute']);

        self::assertEquals(2, $count);
    }

    private function getCountProductModelsWithRemovedAttribute(): CountProductModelsWithRemovedAttributeInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.count_product_models_with_removed_attribute');
    }
}
