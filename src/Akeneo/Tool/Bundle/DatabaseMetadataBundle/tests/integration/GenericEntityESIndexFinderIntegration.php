<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Factory\IndexResultsFactory;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Utils\DateTimeFormat;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityESIndexFinder;

use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
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
class GenericEntityESIndexFinderIntegration extends TestCase
{
    private NativeClient $esClient;
    private Client $esProductClient;
    private Client $assetManager;
    private GenericEntityESIndexFinder $searchEs;

    public function setUp(): void
    {
        //self::bootKernel();
        //parent::setUp();
        //Connection ES
        $clientBuilder = new ClientBuilder();
        $hosts = $_ENV['APP_INDEX_HOSTS'];
        $this->hosts = is_string($hosts) ? [$hosts] : $hosts; //all indexes ES
        $this->esClient = $clientBuilder->setHosts($this->hosts)->build();
        $this->searchEs = new GenericEntityESIndexFinder($this->esClient);

        //$this->resetIndex("akeneo_elasticsearch.client.product_and_product_model");
        $this->resetIndex("akeneo_assetmanager.client.asset");
    }

    /**
     * @dataProvider configProvider
     * @return void
     */
    public function testFindIndex(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        Assert::assertTrue($this->checkIndexExists($entityIndexConfiguration->getTableName()));
    }

    /**
     * @dataProvider configProvider
     * @return void
     */
    public function test_it_results_request_order_by(EntityIndexConfiguration $entityIndexConfiguration)
    {
        $fixtures = [
            ['atmosphere_absorb_atmosphere_1', null],
            ['atmosphere_absorb_atmosphere_2', null],
            ['atmosphere_admete_atmosphere_1', null],
            ['atmosphere_admete_atmosphere_2', null]
        ];
        $tests = new \ArrayIterator($fixtures);
        foreach ($tests as $test) {
            $resultsFormat[] = IndexResultsFactory::initIndexDateResults($test[0], $test[1]);
        }
        $resultsFixtures = new \ArrayIterator($resultsFormat);

        $this->assetManager = $this->get('akeneo_assetmanager.client.asset'); //PASSE
        //$this->assetManager = $this->get($entityIndexConfiguration->getTableName()); //PB NE TROUVE PAS L'INDEX - You have requested a non-existent service "akeneo_assetmanager_asset_test". Did you mean this: "akeneo_assetmanager.client.asset"?
        $this->assetManager->resetIndex();
        //dump($this->assetManager->resetIndex()); //RENVOIE NULL
        $searchEs = new GenericEntityESIndexFinder($this->assetManager);
        $results = $this->searchEs->findAllByOrder($entityIndexConfiguration);
        //dump($searchEs);
        //dump($this->searchEs);
        //$results2 = $searchEs->findAllByOrder($entityIndexConfiguration); //PB Elasticsearch\Common\Exceptions\BadRequest400Exception: {"error":{"root_cause":[{"type":"parsing_exception","reason":"Unknown key for a VALUE_STRING in [index].","line":1,"col":10}],"type":"parsing_exception","reason":"Unknown key for a VALUE_STRING in [index].","line":1,"col":10},"status":400}

        for ($i = 0; $i < 4; $i++) {
            $identifier = substr($results[$i]["identifier"], 0, strrpos($results[$i]["identifier"], '_'));
            $resultsOrderQueryFormat[] = IndexResultsFactory::initIndexDateResults($identifier, null);
        }
        $resultsOrderQuery = new \ArrayIterator($resultsOrderQueryFormat);

        Assert::assertEquals($resultsFixtures, $resultsOrderQuery);
    }

    /**
     * @dataProvider configProviderFilter
     * @return void
     */
    /*public function test_it_results_request_filter_and_order_by(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        $fixtures = [
            ['product_1',null],
            ['product_10', null],
            ['product_100',null],
            ['product_1000', null]
        ];
        $tests = new \ArrayIterator($fixtures);
        foreach ($tests as $test) {
            $resultsFormat[] = IndexResultsFactory::initIndexDateResults($test[0], $test[1]);
        }
        $resultsFixtures = new \ArrayIterator($resultsFormat);
        $indexHost = $this->getParameter('index_hosts');

        $clientBuilder = new ClientBuilder();
        $clientBuilder->setHosts(is_string($indexHost) ? [$indexHost] : $indexHost);

        $client = $clientBuilder->build();
        $searchES = new GenericEntityESIndexFinder($client);
        $results = $searchES->findAllByOrder($entityIndexConfiguration);
        //$results = $this->searchEs->findAllByOrder($entityIndexConfiguration);
        for ($i = 0; $i < 4; $i++) {
            $resultsOrderQueryFormat[] = IndexResultsFactory::initIndexDateResults($results[$i]["identifier"], null);
        }
        $resultsOrderQuery = new \ArrayIterator($resultsOrderQueryFormat);

        Assert::assertEquals($resultsFixtures, $resultsOrderQuery);
    }*/

    public function configProvider(): array
    {
        $assetManagerEs = EntityIndexConfiguration::create(
            ['identifier','updated_at'],
            'akeneo_assetmanager_asset_test',
            'identifier',
            'es'
        );
        $assetManagerEs->setDateFieldName('updated_at');
        $assetManagerEs->setDataProcessing(DateTimeFormat::formatFromInt());
        return [
            'es' => [$assetManagerEs]
        ];
    }

    public function configProviderFilter(): array
    {
        $productEs = EntityIndexConfiguration::create(
            ['id','updated'],
            'akeneo_pim_product_and_product_model',
            'id',
            'es'
        );
        $productEs->setDateFieldName('updated');
        $productEs->setDataProcessing(DateTimeFormat::formatFromString());
        $productEs->setFilterFieldName('document_type="'.addcslashes(ProductInterface::class, '\\').'"');

        return [
            'es' => [$productEs]
        ];
    }

    private function checkIndexExists(string $index): bool
    {
        $indexParams['index'] = $index;
        if (!$this->esClient->indices()->exists($indexParams)) {
            return false;
        }
        return true;
    }

    /**
     * @param string $str
     * @return void
     */
    public function resetIndex(string $str): void
    {
        $clientProduct = $this->getContainer()->get($str);
        $clientProduct->resetIndex();
    }

    protected function getConfiguration()
    {
        //return $this->catalog->useFunctionalCatalog('catalog_modeling');
        return $this->catalog->useMinimalCatalog();
    }
}
