<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;

class CountProductsWithRemovedAttributeIntegration extends WithRemovedAttributeTestCase
{
    public function test_it_only_count_products_with_a_removed_attribute()
    {
        $this->removeAttribute('an_attribute');
        $this->removeAttribute('a_third_attribute');

        $count = $this->getCountProductsWithRemovedAttribute()->count(['an_attribute', 'a_third_attribute']);

        self::assertEquals(2, $count);
    }

    private function getCountProductsWithRemovedAttribute(): CountProductsWithRemovedAttributeInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.count_products_with_removed_attribute');
    }
}
