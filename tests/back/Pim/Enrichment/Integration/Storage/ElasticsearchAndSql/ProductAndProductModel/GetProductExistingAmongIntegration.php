<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetExistingProductUuids;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductExistingAmongIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
    }

    public function test_it_gets_existing_product_uuids(): void
    {
        $productUuids = $this->getProductExistingAmong()->among(['product_1', 'product_2', 'product_unknown']);

        $this->assertCount(2, $productUuids);
        $this->assertContainsOnlyInstancesOf(UuidInterface::class, $productUuids);
    }

    private function getProductExistingAmong(): GetExistingProductUuids
    {
        return $this->get(GetExistingProductUuids::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
