<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetProductUuidsNotSynchronisedBetweenEsAndMysql;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductNotSynchronisedBetweenEsAndMysqlIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
    }

    public function test_it_get_diff_product_uuids_without_diff(): void
    {
        $diff = $this->getProductNotSynchronisedBetweenEsAndMysql()->byBatchesOf(100);

        $this->assertCount(0, [...$diff][0]);
    }

    public function test_it_get_diff_product_uuids_with_batch_size(): void
    {
        $query = <<<SQL
UPDATE pim_catalog_product
SET updated = DATE_SUB(NOW(), INTERVAL 1 DAY)
LIMIT 3;
SQL;
        $this->getDbConnection()->executeQuery($query);

        $diff = $this->getProductNotSynchronisedBetweenEsAndMysql()->byBatchesOf(2);
        $result = [...$diff];

        $this->assertCount(3, $result);
        $this->assertContainsOnlyInstancesOf(UuidInterface::class, $result[0]);
        $this->assertContainsOnlyInstancesOf(UuidInterface::class, $result[1]);
        $this->assertContainsOnlyInstancesOf(UuidInterface::class, $result[2]);
    }

    private function getProductNotSynchronisedBetweenEsAndMysql(): GetProductUuidsNotSynchronisedBetweenEsAndMysql
    {
        return $this->get(GetProductUuidsNotSynchronisedBetweenEsAndMysql::class);
    }

    private function getDbConnection(): Connection
    {
        return $this->get('database_connection');
    }

    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
