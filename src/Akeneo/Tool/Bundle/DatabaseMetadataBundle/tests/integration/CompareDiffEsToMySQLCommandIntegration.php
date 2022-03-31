<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Utils\DateTimeFormat;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityESIndexFinder;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityMySQLIndexFinder;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;
use Elasticsearch\ClientBuilder;
use PHPUnit\Framework\Assert;

/**
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
class CompareDiffEsToMySQLCommandIntegration extends TestCase
{
    private const DB_REFERENCE_FILE = __DIR__ . '/../Resources/referenceOutput.jsonl';

    private GenericEntityMySQLIndexFinder $searchMySql;

    public function setUp(): void
    {
        static::bootKernel();
        parent::setUp();

        $this->searchMySql = $this->get(GenericEntityMySQLIndexFinder::class);
        $this->runResetIndexesCommand();
    }

    private function runResetIndexesCommand(): void
    {
        $commandLauncher = new CommandLauncher(static::$kernel);
        $exitCode = $commandLauncher->execute('akeneo:elasticsearch:reset-indexes', null, ['inputs' => ['yes']]);
        $this->assertSame(0, $exitCode);
    }


    /**
     * @dataProvider configProviderDB
     */
    public function test_valid_configuration_MySQL(EntityIndexConfiguration $configMySQL): void
    {
        $results = $this->searchMySql->findAllByOrder($configMySQL);
        Assert::assertIsIterable($results);
    }

    /**
     * @dataProvider configProviderEs $configEs
     */
    public function test_valid_configuration_ES(EntityIndexConfiguration $configEs): void
    {
        $clientBuilder = new ClientBuilder();
        $indexHost = $this->getParameter('index_hosts');
        $clientBuilder->setHosts(is_string($indexHost) ? [$indexHost] : $indexHost);
        $client = $clientBuilder->build();
        $searchEs = new GenericEntityESIndexFinder($client);
        $results = $searchEs->findAllByOrder($configEs);
        Assert::assertIsIterable($results);
    }

    public function test_it_dumpItemToJsonFiles(): void
    {
        $tests = new \ArrayIterator([
            ['identifer'=>'i1', 'date'=>Date('2022-01-01 00:00:00')],
            ['identifer'=>'i2', 'date'=>Date('2022-01-01 00:00:00')]
        ]);

        $file = __DIR__ . '/../Resources/test'.date('_Y_m_d_H_i_s').'.jsonl';
        $commandDiff = $this->getContainer()->get('akeneo.pim.migration_diff.command');
        $commandDiff->dumpItemToJsonFiles($tests, $file);

        Assert::assertFileEquals(self::DB_REFERENCE_FILE, $file);
    }

    public function configProviderDB(): array
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

    public function configProviderEs(): array
    {
        $assetManagerEs = EntityIndexConfiguration::create(
            ['identifier', 'updated_at'],
            'akeneo_assetmanager_asset_test',
            'identifier',
            'es'
        );
        return [
            'es' => [$assetManagerEs]
        ];
    }

    public function configProviderEsWithoutDate(): array
    {
        return [
            'es' => [
                EntityIndexConfiguration::create(
                    ['id'],
                    'akeneo_pim_product_proposal_test',
                    'id',
                    'es'
                )
            ]
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
