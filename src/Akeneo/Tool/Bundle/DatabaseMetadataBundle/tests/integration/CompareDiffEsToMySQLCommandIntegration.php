<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityESIndexFinder;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityMySQLIndexFinder;

use Doctrine\DBAL\Connection;
use Elasticsearch\Client as NativeClient;
use Elasticsearch\ClientBuilder;
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
class CompareDiffEsToMySQLCommandIntegration extends KernelTestCase
{
    private const DB_REFERENCE_FILE = __DIR__ . '/../Resources/referenceOutput.jsonl';

    private NativeClient $esClient;
    /** @var Connection */
    private $connection;
    private $hosts;
    private GenericEntityMySQLIndexFinder $searchMySql;
    private GenericEntityESIndexFinder $searchEs;

    public function setUp(): void
    {
        //create and launch the kernel to test
        self::bootKernel();
        //Connection to Database
        $this->connection = $this->get('database_connection');
        $this->searchMySql = new GenericEntityMySQLIndexFinder($this->connection);
        //Connection ES
        $clientBuilder = new ClientBuilder();
        $hosts = $_ENV['APP_INDEX_HOSTS'];
        $this->hosts = is_string($hosts) ? [$hosts] : $hosts; //all indexes ES
        $this->esClient = $clientBuilder->setHosts($this->hosts)->build();
        $this->searchEs = new GenericEntityESIndexFinder($this->esClient);
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
        $results = $this->searchEs->findAllByOrder($configEs);
        Assert::assertIsIterable($results);
    }

    /**
     * Check if good results
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
     * Check if good results
     * @dataProvider configProviderEs2
     * @return void
     */
    public function test_it_readEsData_returns(EntityIndexConfiguration $configEs): void
    {
        $res = [];
        $test = new \ArrayIterator($res);
        $results = $this->searchEs->findAllByOrder($configEs);

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
        $assetManagerMySql = EntityIndexConfiguration::create(
            ['identifier', 'updated_at'],
            'akeneo_asset_manager_asset',
            'identifier',
            'mysql');
        return [
            'mysql' => [$assetManagerMySql]
        ];
    }

    public function configProviderEs(): array
    {
        $assetManagerEs = EntityIndexConfiguration::create(
            ['identifier', 'updated_at'],
            'akeneo_assetmanager_asset',
            'identifier',
            'es');
        return [
            'es' => [$assetManagerEs]
        ];
    }

    public function configProviderEs2(): array
    {
        $productProposalEs = EntityIndexConfiguration::create(
            ['id'],
            'akeneo_pim_product_proposal',
            'id',
            'es');
        return [
            'es' => [$productProposalEs]
        ];
    }


    protected function get(string $service): ?object
    {
        return static::$kernel->getContainer()->get($service);
    }
}
