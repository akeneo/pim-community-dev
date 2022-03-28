<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Factory\IndexResultsFactory;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;

use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Utils\DateTimeFormat;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityMySQLIndexFinder;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;

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

    protected function setUp(): void
    {
        /*$this->runResetIndexesCommand();*/
        parent::setUp();

        $this->query = $this->get(GenericEntityMySQLIndexFinder::class);
    }

    /*private function runResetIndexesCommand(): void
    {
        $commandLauncher = new CommandLauncher(static::$kernel); // static::bootKernel(); //static::$kernel
        $exitCode = $commandLauncher->execute('akeneo:elasticsearch:reset-indexes', null, ['inputs' => ['yes']]);
        $this->assertSame(0, $exitCode);
    }*/


    /**
     * 0@dataProvider configProvider
     * @return void
     */
    public function test_it_table_mysql_exists(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        Assert::assertTrue($this->tableExists($entityIndexConfiguration->getTableName()));
    }

    /**
     * 0@dataProvider configProvider
     * @return void
     */
    public function test_it_columns_mysql_exists(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        foreach ($entityIndexConfiguration->getColumnsName() as $column) {
            if(substr($column,0,3)=== "CON"){
                $column = substr($column, strrpos($column, 'AS')+ strlen('as'));
            }
            Assert::assertTrue($this->columnsExists($entityIndexConfiguration->getTableName(), $column));
        }
    }

    /**
     * 0@dataProvider configProvider
     * @return void
     */
    public function test_it_results_request_order_by(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        $fixtures = [['product_1', null],
            ['product_10', null],
            ['product_100', null],
            ['product_101', null]];
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
     * 0@dataProvider configProviderFilterOrderBy
     * @return void
     */
    public function test_it_results_request_filter_and_is_ordered(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        $fixtures = [['product_1', null],
            ['product_2', null],
            ['product_3', null],
            ['product_4', null]];
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
        $connection = $this->get('database_connection');
        $rows = $connection->executeQuery(
            'SHOW TABLES LIKE :tableName',
            [
                'tableName' => $tableName,
            ]
        )->fetchAllAssociative();

        return count($rows) >= 1;
    }

    private function columnsExists(string $tableName, string $columnName): bool
    {
        $connection = $this->get('database_connection');
        $rows = $connection->fetchAllAssociative(
            sprintf('SHOW COLUMNS FROM %s LIKE :columnName', $tableName),
            [
                'columnName' => $columnName,
            ]
        );

        return count($rows) >= 1;
    }

    protected function getConfiguration()
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
        //return $this->catalog->useMinimalCatalog();
        //return $this->catalog->useTechnicalCatalog();
    }
}
