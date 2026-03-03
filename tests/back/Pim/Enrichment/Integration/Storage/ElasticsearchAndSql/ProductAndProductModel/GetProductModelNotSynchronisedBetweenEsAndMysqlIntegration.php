<?php

declare(strict_types=1);

namespace AkeneoTest\Pim\Enrichment\Integration\Storage\ElasticsearchAndSql\ProductAndProductModel;

use Akeneo\Pim\Enrichment\Bundle\Storage\ElasticsearchAndSql\ProductAndProductModel\GetProductModelCodesNotSynchronisedBetweenEsAndMysql;
use Akeneo\Test\Integration\TestCase;
use Doctrine\DBAL\Connection;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductModelNotSynchronisedBetweenEsAndMysqlIntegration extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->get('akeneo_integration_tests.fixture.loader.product_and_product_model_with_removed_attribute')->load();
    }

    public function test_it_gets_diff_product_models_without_diff(): void
    {
        $diff = $this->getProductModelNotSynchronisedBetweenEsAndMysql()->byBatchesOf(100);

        $this->assertCount(0, [...$diff][0]);
    }

    public function test_it_gets_diff_product_models_with_batch_size(): void
    {
        $query = <<<SQL
UPDATE pim_catalog_product_model
SET updated = DATE_SUB(NOW(), INTERVAL 1 DAY);
SQL;
        $this->getDbConnection()->executeQuery($query);

        $diff = $this->getProductModelNotSynchronisedBetweenEsAndMysql()->byBatchesOf(1);
        $result = [...$diff];

        $this->assertEquals([['a_product_model'], ['a_second_product_model']], $result);
    }

    private function getProductModelNotSynchronisedBetweenEsAndMysql(): GetProductModelCodesNotSynchronisedBetweenEsAndMysql
    {
        return $this->get(GetProductModelCodesNotSynchronisedBetweenEsAndMysql::class);
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
