<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Factory\IndexResultsFactory;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Utils\DateTimeFormat;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityMySQLIndexFinder;

use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class GenericEntityMySQLIndexFinderIntegration extends TestCase
{
    private GenericEntityMySQLIndexFinder $query;
    private Connection $connection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->query = $this->get(GenericEntityMySQLIndexFinder::class);
        $this->connection = $this->get('database_connection');
    }


    /**
     * @dataProvider configProvider
     * @return void
     */
    public function test_it_table_mysql_exists(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        Assert::assertTrue($this->tableExists($entityIndexConfiguration->getTableName()));
    }

    /**
     * @dataProvider configProvider
     * @return void
     */
    public function test_it_columns_mysql_exists(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        foreach ($entityIndexConfiguration->getColumnsName() as $column) {
            if(substr($column,0,3)=== "CON"){
                $column = substr($column, strrpos($column, 'AS') + strlen('as')+1);
            }
            Assert::assertTrue($this->columnExists($entityIndexConfiguration->getTableName(), $column));
        }
    }

    /**
     * @dataProvider configProvider
     * @return void
     */
    public function test_it_results_request_order_by(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        $fixtures = [['product_1', null], ['product_10', null], ['product_100', null], ['product_101', null]];
        $tests = new \ArrayIterator($fixtures);
        foreach ($tests as $test) {
            $resultsFormat[] = IndexResultsFactory::initIndexDateResults($test[0], $test[1]);
        }
        $resultsFixtures = new \ArrayIterator($resultsFormat);

        $resultsQuery = $this->query->findAllByOrder($entityIndexConfiguration);
        for ($i = 0; $i < 4; $i++) {
            $resultsOrderQueryFormat[] = IndexResultsFactory::initIndexDateResults($resultsQuery[$i]["identifier"], null);
        }
        $resultsOrderQuery = new \ArrayIterator($resultsOrderQueryFormat);

        Assert::assertEquals($resultsFixtures, $resultsOrderQuery);
    }

    /**
     * @dataProvider configProviderFilterOrderBy
     * @return void
     */
    public function test_it_results_request_filter_and_is_ordered(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        $fixtures = [['product_1', null], ['product_2', null], ['product_3', null], ['product_4', null]];
        $tests = new \ArrayIterator($fixtures);
        foreach ($tests as $test) {
            $resultsFormat[] = IndexResultsFactory::initIndexDateResults($test[0], $test[1]);
        }
        $resultsFixtures = new \ArrayIterator($resultsFormat);

        $resultsOrderFilterQueryFormat = [];
        $resultsQuery = $this->query->findAllByOrder($entityIndexConfiguration);
        foreach ($resultsQuery as $value) {
            $resultsOrderFilterQueryFormat[] = IndexResultsFactory::initIndexDateResults($value["identifier"], null);
        }
        $resultsOrderFilterQuery = new \ArrayIterator($resultsOrderFilterQueryFormat);

        Assert::assertEquals($resultsFixtures, $resultsOrderFilterQuery);
    }

    public function configProvider(): array
    {
        $productMysql = EntityIndexConfiguration::create(
            ['CONCAT("product_",id) AS id', 'updated'],
            'pim_catalog_product', //'akeneo_asset_manager_asset'
            'id',
            'mysql'
        );
        $productMysql->setDateFieldName('updated');
        $productMysql->setDataProcessing(DateTimeFormat::formatFromString());
        return [
            'mysql' => [$productMysql]
        ];
    }

    public function configProviderFilterOrderBy(): array
    {
        $productMysql = EntityIndexConfiguration::create(
            ['CONCAT("product_",id) AS id', 'updated'],
            'pim_catalog_product',
            'id',
            'mysql'
        );
        $productMysql->setDateFieldName('updated');
        $productMysql->setDataProcessing(DateTimeFormat::formatFromString());
        $productMysql->setFilterFieldName("id < 5");
        return [
            'mysql' => [$productMysql]
        ];
    }


    private function tableExists(string $tableName): bool
    {
        $rows = $this->connection->executeQuery(
            'SHOW TABLES LIKE :tableName',
            [
                'tableName' => $tableName,
            ]
        )->fetchAllAssociative();

        return count($rows) >= 1;
    }

    private function columnExists(string $tableName, string $columnName): bool
    {
        return 1 === $this->connection->executeQuery(sprintf("SHOW COLUMNS FROM %s LIKE '%s'", $tableName,
                $columnName))->rowCount();
    }

    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
