<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Component\Product\Query\CountProductsWithRemovedAttributeInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

class CountProductsWithRemovedAttributeIntegration extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
    }

    public function test_it_only_count_products_with_a_removed_attribute()
    {
        $count = $this->getCountProductsWithRemovedAttribute()->count(['an_attribute', 'a_third_attribute']);

        self::assertEquals(3, $count);
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function getCountProductsWithRemovedAttribute(): CountProductsWithRemovedAttributeInterface
    {
        return $this->get('akeneo.pim.enrichment.product.query.count_products_with_removed_attribute');
    }
}
