<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductModelInterface;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Factory\IndexResultsFactory;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Utils\DateTimeFormat;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityESIndexFinder;

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
class GenericEntityESIndexFinderIntegration extends TestCase
{
    private NativeClient $esClient;

    private GenericEntityESIndexFinder $searchEs;

    public function setUp(): void
    {
        //Connection ES
        $clientBuilder = new ClientBuilder();
        $hosts = $_ENV['APP_INDEX_HOSTS'];
        $this->hosts = is_string($hosts) ? [$hosts] : $hosts; //all indexes ES
        $this->esClient = $clientBuilder->setHosts($this->hosts)->build();
        $this->searchEs = new GenericEntityESIndexFinder($this->esClient);

        $this->resetIndex("akeneo_elasticsearch.client.product_and_product_model");
    }

    /**
     * @dataProvider configProviderFilter
     * @return void
     */
    public function testFindIndex(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        Assert::assertTrue($this->checkIndexExists($entityIndexConfiguration->getTableName()));
    }

    /**
     * @dataProvider configProviderFilter
     * @return void
     */
    public function test_it_results_request_order_by(EntityIndexConfiguration $entityIndexConfiguration)
    {
        $fixtures = [
            ['product_model_1', null],
            ['product_model_2', null],
            ['product_model_3', null],
            ['product_model_4', null]
        ];
        $tests = new \ArrayIterator($fixtures);
        foreach ($tests as $test) {
            $resultsFormat[] = IndexResultsFactory::initIndexDateResults($test[0], $test[1]);
        }
        $resultsFixtures = new \ArrayIterator($resultsFormat);

        $results = $this->searchEs->findAllByOrder($entityIndexConfiguration);

        for ($i = 0; $i < 4; $i++) {
            //$identifier = substr($results[$i]["identifier"], 0, strrpos($results[$i]["identifier"], '_'));
            $resultsOrderQueryFormat[] = IndexResultsFactory::initIndexDateResults($results[$i]["id"], null);
        }
        $resultsOrderQuery = new \ArrayIterator($resultsOrderQueryFormat);

        Assert::assertEquals($resultsFixtures, $resultsOrderQuery);
    }

    /**
     * @dataProvider configProviderFilter
     * @return void
     */
    public function test_it_results_request_filter_and_order_by(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        $fixtures = [
            ['product_model_1', null],
            ['product_model_2', null],
            ['product_model_3', null],
            ['product_model_4', null]
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
    }

    public function configProviderFilter(): array
    {
        $productEs = EntityIndexConfiguration::create(
            ['id','updated'],
            'akeneo_pim_product_and_product_model_test',
            'id',
            'es'
        );
        $productEs->setDateFieldName('updated');
        $productEs->setDataProcessing(DateTimeFormat::formatFromString());
        $productEs->setFilterFieldName('document_type="'.addcslashes(ProductModelInterface::class, '\\').'"');

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
        return $this->catalog->useTechnicalCatalog();
    }

}
