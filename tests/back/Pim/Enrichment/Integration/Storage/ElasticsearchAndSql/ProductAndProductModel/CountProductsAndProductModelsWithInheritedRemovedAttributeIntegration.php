<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsAndProductModelsWithInheritedRemovedAttributeInterface;

class CountProductsAndProductModelsWithInheritedRemovedAttributeIntegration extends WithRemovedAttributeTestCase
{
    public function test_it_only_count_products_and_product_models_with_an_inherited_removed_attribute()
    {
        $this->removeAttribute('an_attribute');
        $this->removeAttribute('a_third_attribute');

        $count = $this->getCountProductsAndProductModelsWithInheritedRemovedAttribute()->count(['an_attribute', 'a_third_attribute']);

        self::assertEquals(1, $count);
    }

    private function getCountProductsAndProductModelsWithInheritedRemovedAttribute(): CountProductsAndProductModelsWithInheritedRemovedAttributeInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.count_products_and_product_models_with_inherited_removed_attribute');
    }
}
