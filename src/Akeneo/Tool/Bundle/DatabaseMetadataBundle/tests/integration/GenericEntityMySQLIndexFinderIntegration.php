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
     */
    public function test_it_results_request_order_by(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        $resultsFixtures = new \ArrayIterator(
            array_map(
                function ($item) {
                    return IndexResultsFactory::initIndexFormatDataResults($item, null);
                },
                ["product_1", "product_10", "product_100", "product_101"]));

        $resultsQuery = $this->query->findAllByOrder($entityIndexConfiguration);
        $resultsOrderQueryFormat = [];
        for ($i = 0; $i < sizeof($resultsFixtures); $i++) {
            $resultsOrderQueryFormat[] = IndexResultsFactory::initIndexFormatDataResults($resultsQuery[$i]["identifier"], null);
        }
        $resultsOrderQuery = new \ArrayIterator($resultsOrderQueryFormat);

        Assert::assertEquals($resultsFixtures, $resultsOrderQuery);
    }

    /**
     * @dataProvider configProviderFilterOrderBy
     */
    public function test_it_results_request_filter_and_is_ordered(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        $resultsFixtures = new \ArrayIterator(array_map(
            function ($item) {
                return IndexResultsFactory::initIndexFormatDataResults($item, null);
            },
            ["product_1", "product_2", "product_3", "product_4"]));

        $resultsOrderFilterQueryFormat = [];
        $resultsQuery = $this->query->findAllByOrder($entityIndexConfiguration);
        foreach ($resultsQuery as $value) {
            $resultsOrderFilterQueryFormat[] = IndexResultsFactory::initIndexFormatDataResults($value["identifier"], null);
        }
        $resultsOrderFilterQuery = new \ArrayIterator($resultsOrderFilterQueryFormat);

        Assert::assertEquals($resultsFixtures, $resultsOrderFilterQuery);
    }

    public function configProvider(): array
    {
        $productMysql = EntityIndexConfiguration::create(
            ['CONCAT("product_",id) AS id', 'updated'],
            'pim_catalog_product',
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

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useFunctionalCatalog('catalog_modeling');
    }
}
