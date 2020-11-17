<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;

class CountProductsWithRemovedAttributeIntegration extends WithRemovedAttributeTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures();
    }

    public function test_it_only_count_products_with_a_removed_attribute()
    {
        $this->removeAttribute(self::ATTRIBUTE_ONLY_ON_ONE_PRODUCT);

        $expectedCount = 1;
        $count = $this->getCountProductsWithRemovedAttribute()->count([self::ATTRIBUTE_ONLY_ON_ONE_PRODUCT]);

        self::assertEquals($expectedCount, $count);
    }

    public function test_it_does_not_count_product_models()
    {
        $this->removeAttribute(self::ATTRIBUTE_ONLY_ON_ONE_PRODUCT_MODEL);

        $expectedCount = 0;
        $count = $this->getCountProductsWithRemovedAttribute()->count([self::ATTRIBUTE_ONLY_ON_ONE_PRODUCT_MODEL]);

        self::assertEquals($expectedCount, $count);
    }

    private function getCountProductsWithRemovedAttribute(): CountProductsWithRemovedAttributeInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.count_products_with_removed_attribute');
    }
}
