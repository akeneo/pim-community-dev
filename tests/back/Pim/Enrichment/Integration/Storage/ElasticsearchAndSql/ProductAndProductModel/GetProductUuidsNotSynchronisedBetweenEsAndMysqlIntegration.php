<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetProductUuidsNotSynchronisedBetweenEsAndMysql;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Lazy\LazyUuidFromString;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsNotSynchronisedBetweenEsAndMysqlIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    public function test_it_gets_diff_products_without_diff(): void
    {
        $diff = $this->getProductUuidsNotSynchronisedBetweenEsAndMysql()->byBatchesOf(100);

        $this->assertCount(0, [...$diff][0]);
    }

    public function test_it_gets_diff_products_with_batch_size(): void
    {
        $query = <<<SQL
UPDATE pim_catalog_product
SET updated = DATE_SUB(NOW(), INTERVAL 1 DAY);
SQL;
        $this->getDbConnection()->executeQuery($query);

        $diff = $this->getProductUuidsNotSynchronisedBetweenEsAndMysql()->byBatchesOf(1);
        $results = [...$diff];

        $this->assertCount(4, $results);
        foreach ($results as $resultLine) {
            $this->assertContainsOnlyInstancesOf(LazyUuidFromString::class, $resultLine);
        }
    }

    private function getProductUuidsNotSynchronisedBetweenEsAndMysql(): GetProductUuidsNotSynchronisedBetweenEsAndMysql
    {
        return $this->get(GetProductUuidsNotSynchronisedBetweenEsAndMysql::class);
    }

    private function getDbConnection(): Connection
    {
        return $this->get('database_connection');
    }
}
