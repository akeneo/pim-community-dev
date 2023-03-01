<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetAllProductUuids;
use Akeneo\Test\Integration\TestCase;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetAllProductIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
    }

    public function test_it_gets_all_product_uuids(): void
    {
        $productUuids = $this->getAllProduct()->byBatchesOf(2);
        $result = [...$productUuids];

        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(UuidInterface::class, $result[0]);
        $this->assertContainsOnlyInstancesOf(UuidInterface::class, $result[1]);
        $this->assertContainsOnlyInstancesOf(UuidInterface::class, $result[2]);
    }

    private function getAllProduct(): GetAllProductUuids
    {
        return $this->get(GetAllProductUuids::class);
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
