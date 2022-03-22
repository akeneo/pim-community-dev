<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Factory\IndexResultsFactory;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;

use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Utils\DateTimeFormat;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityMySQLIndexFinder;
use Doctrine\DBAL\Connection;
use PHPUnit\Framework\Assert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class GenericEntityMySQLIndexFinderTest extends KernelTestCase
{

    /** @var Connection */
    private $connection;
    private GenericEntityMySQLIndexFinder $searchMySql;

    public function setUp(): void
    {
        //create and launch the kernel to test
        self::bootKernel();
        //Connection to Database
        $this->connection = $this->get('database_connection');
        $this->searchMySql = new GenericEntityMySQLIndexFinder($this->connection);
    }

    /**
     * 0@dataProvider configProvider
     * @return void
     */
    public function test_FindAllByOrder(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        $request = $this->connection->createQueryBuilder()
            ->select($entityIndexConfiguration->getColumnsName())
            ->from($entityIndexConfiguration->getTableName());

        if ($entityIndexConfiguration->getFilterFieldName() !== null) {
            $request->andwhere($entityIndexConfiguration->getFilterFieldName());
        }
        $request->orderBy($entityIndexConfiguration->getIdentifierFieldName(), 'ASC');

        $results =  $this->connection->executeQuery($request)->iterateAssociative();

        Assert::assertIsIterable($results);
    }

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
            Assert::assertTrue($this->columnsExists($entityIndexConfiguration->getTableName(), $column));
        }
    }

    /**
     * 0@dataProvider configProvider
     * @return void
     */
    public function test_it_results_request_order_by(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        $fixtures = [['atmosphere_absorb_atmosphere_1', null],
            ['atmosphere_absorb_atmosphere_2', null],
            ['atmosphere_admete_atmosphere_1', null],
            ['atmosphere_admete_atmosphere_2', null]];
        $tests = new \ArrayIterator($fixtures);
        foreach ($tests as $test) {
            $resultsFormat[] = IndexResultsFactory::initIndexDateResults($test[0], $test[1]);
        }
        $resultsFixtures = new \ArrayIterator($resultsFormat);

        $resultsQuery = $this->searchMySql->findAllByOrder($entityIndexConfiguration);
        for ($i = 0; $i < 4; $i++) {
            $identifier = substr($resultsQuery[$i]["identifier"], 0, strrpos($resultsQuery[$i]["identifier"], '_'));
            $resultsOrderQueryFormat[] = IndexResultsFactory::initIndexDateResults($identifier, null);
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
        $fixtures = [['atmosphere_absorb_atmosphere_1', null],
            ['atmosphere_absorb_atmosphere_2', null],
            ['atmosphere_admete_atmosphere_1', null],
            ['atmosphere_admete_atmosphere_2', null]];
        $tests = new \ArrayIterator($fixtures);
        foreach ($tests as $test) {
            $resultsFormat[] = IndexResultsFactory::initIndexDateResults($test[0], $test[1]);
        }
        $resultsFixtures = new \ArrayIterator($resultsFormat);

        $resultsQuery =$this->searchMySql->findAllByOrder($entityIndexConfiguration);
        foreach ($resultsQuery as $value) {
            $identifier = substr($value["identifier"], 0, strrpos($value["identifier"], '_'));
            $resultsOrderFilterQueryFormat[] = IndexResultsFactory::initIndexDateResults($identifier, null);
        }
        $resultsOrderFilterQuery = new \ArrayIterator($resultsOrderFilterQueryFormat);

        Assert::assertEquals($resultsFixtures, $resultsOrderFilterQuery);
    }

    public function configProvider(): array
    {
        $assetManagerMysql = EntityIndexConfiguration::create(
            ['identifier', 'updated_at'],
            'akeneo_asset_manager_asset',
            'identifier',
            'mysql'
        );
        $assetManagerMysql->setDateFieldName('updated_at');
        $assetManagerMysql->setDataProcessing(DateTimeFormat::formatFromString());
        return [
            'mysql' => [$assetManagerMysql]
        ];
    }
    public function configProviderFilterOrderBy(): array
    {
        $assetManagerMysql = EntityIndexConfiguration::create(
            ['identifier', 'updated_at'],
            'akeneo_asset_manager_asset',
            'identifier',
            'mysql'
        );
        $assetManagerMysql->setDateFieldName('updated_at');
        $assetManagerMysql->setDataProcessing(DateTimeFormat::formatFromString());
        $assetManagerMysql->setFilterFieldName("identifier LIKE 'atmosphere%'");
        return [
            'mysql' => [$assetManagerMysql]
        ];
    }

    protected function get(string $service): ?object
    {
        return static::$kernel->getContainer()->get($service);
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

    private function columnsExists(string $tableName, string $columnName): bool
    {
        $rows = $this->connection->fetchAllAssociative(
            sprintf('SHOW COLUMNS FROM %s LIKE :columnName', $tableName),
            [
                'columnName' => $columnName,
            ]
        );

        return count($rows) >= 1;
    }
}
