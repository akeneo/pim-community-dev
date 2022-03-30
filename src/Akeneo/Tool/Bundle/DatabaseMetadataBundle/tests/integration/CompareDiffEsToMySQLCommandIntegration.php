<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Utils\DateTimeFormat;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityESIndexFinder;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityMySQLIndexFinder;
use Akeneo\Test\IntegrationTestsBundle\Launcher\CommandLauncher;


use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Elasticsearch\Client as NativeClient;
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
        //create and launch the kernel to test
        static::bootKernel();
        parent::setUp();

        $this->searchMySql = $this->get(GenericEntityMySQLIndexFinder::class);
        $this->runResetIndexesCommand();

    }

    private function runResetIndexesCommand(): void
    {
        $commandLauncher = new CommandLauncher(static::$kernel); // static::bootKernel(); //static::$kernel
        $exitCode = $commandLauncher->execute('akeneo:elasticsearch:reset-indexes', null, ['inputs' => ['yes']]);
        $this->assertSame(0, $exitCode);
    }


    /**
     * @dataProvider configProviderDB
     * @return void
     */
    public function test_can_read_mysql_data(EntityIndexConfiguration $configMySQL): void
    {
        $results = $this->searchMySql->findAllByOrder($configMySQL);
        Assert::assertIsIterable($results);
    }

    /**
     * @dataProvider configProviderEs $configEs
     * @return void
     */
    public function test_can_read_es_data(EntityIndexConfiguration $configEs): void
    {
        $clientBuilder = new ClientBuilder();
        $indexHost = $this->getParameter('index_hosts');
        $clientBuilder->setHosts(is_string($indexHost) ? [$indexHost] : $indexHost);
        $client = $clientBuilder->build();
        $searchEs = new GenericEntityESIndexFinder($client);
        $results = $searchEs->findAllByOrder($configEs);
        Assert::assertIsIterable($results);
    }

    /**
     * @dataProvider configProviderDB
     * @return void
     */
    public function test_it_readMySQLData_returns(EntityIndexConfiguration $configMySQL): void
    {
        $res = [];
        $test = new \ArrayIterator($res);

        $results = $this->searchMySql->findAllByOrder($configMySQL);

        Assert::assertEquals($test,$results);
    }

    /**
     * @dataProvider configProviderEsWithoutDate
     * @return void
     */
    public function test_it_readEsData_returns(EntityIndexConfiguration $configEs): void
    {
        $res = [];
        $test = new \ArrayIterator($res);
        $clientBuilder = new ClientBuilder();
        $indexHost = $this->getParameter('index_hosts');
        $clientBuilder->setHosts(is_string($indexHost) ? [$indexHost] : $indexHost);
        $client = $clientBuilder->build();
        $searchEs = new GenericEntityESIndexFinder($client);
        $results = $searchEs->findAllByOrder($configEs);

        Assert::assertEquals($test,$results);
    }

    public function test_it_dumpItemToJsonFiles_result()
    {
        $fixtures = [
            ['identifer'=>'i1', 'date'=>Date('2022-01-01 00:00:00')],
            ['identifer'=>'i2', 'date'=>Date('2022-01-01 00:00:00')]
        ];
        $tests = new \ArrayIterator($fixtures);

        $file = __DIR__ . '/../Resources/test'.date('_Y_m_d_H_i_s').'.jsonl';
        foreach ($tests as $it => $data) {
            file_put_contents($file, json_encode($data) . "\n", FILE_APPEND);
        }
        Assert::assertFileEquals(self::DB_REFERENCE_FILE, $file);
    }

    public function configProviderDB(): array
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

    public function configProviderEs(): array
    {
        $assetManagerEs = EntityIndexConfiguration::create(
            ['identifier', 'updated_at'],
            'akeneo_assetmanager_asset_test',
            'identifier',
            'es');
        return [
            'es' => [$assetManagerEs]
        ];
    }

    public function configProviderEsWithoutDate(): array
    {
        $productProposalEs = EntityIndexConfiguration::create(
            ['id'],
            'akeneo_pim_product_proposal_test',
            'id',
            'es');
        return [
            'es' => [$productProposalEs]
        ];
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useTechnicalCatalog();
    }
}
