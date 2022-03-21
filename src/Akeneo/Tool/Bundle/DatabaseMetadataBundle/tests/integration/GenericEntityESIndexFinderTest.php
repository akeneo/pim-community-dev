<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Factory\IndexResultsFactory;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Model\EntityIndexConfiguration;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Domain\Utils\DateTimeFormat;
use Akeneo\Tool\Bundle\DatabaseMetadataBundle\Query\GenericEntityESIndexFinder;

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
class GenericEntityESIndexFinderTest extends KernelTestCase
{
    private NativeClient $esClient;
    public function setUp(): void
    {
        self::bootKernel();
        //Connection ES
        $clientBuilder = new ClientBuilder();
        $hosts = $_ENV['APP_INDEX_HOSTS'];
        $this->hosts = is_string($hosts) ? [$hosts] : $hosts; //all indexes ES
        $this->esClient = $clientBuilder->setHosts($this->hosts)->build();
        $this->searchEs = new GenericEntityESIndexFinder($this->esClient);
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
        foreach($tests as $test)
        {
            $resultsFormat[] = IndexResultsFactory::initIndexDateResults($test[0],$test[1]);
        }
        $resultsFixtures = new \ArrayIterator($resultsFormat);

        $results = $this->searchEs->findAllByOrder($entityIndexConfiguration);

        for($i = 0; $i < 4; $i++)
        {
            $identifier = substr($results[$i]["identifier"],0,strrpos($results[$i]["identifier"], '_'));
            $resultsOrderQueryFormat[] = IndexResultsFactory::initIndexDateResults($identifier,null);
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
            ['product_1',null],
            ['product_10', null],
            ['product_100',null],
            ['product_1000', null]
        ];
        $tests = new \ArrayIterator($fixtures);
        foreach($tests as $test)
        {
            $resultsFormat[] = IndexResultsFactory::initIndexDateResults($test[0],$test[1]);
        }
        $resultsFixtures = new \ArrayIterator($resultsFormat);

        $results = $this->searchEs->findAllByOrder($entityIndexConfiguration);
        for($i = 0; $i < 4; $i++)
        {
            $resultsOrderQueryFormat[] = IndexResultsFactory::initIndexDateResults($results[$i]["identifier"],null);
        }
        $resultsOrderQuery = new \ArrayIterator($resultsOrderQueryFormat);

        Assert::assertEquals($resultsFixtures, $resultsOrderQuery);
    }

    public function configProvider(): array
    {
        $assetManagerEs = EntityIndexConfiguration::create(
            ['identifier','updated_at'],
            'akeneo_assetmanager_asset',
            'identifier',
            'es');
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
            'es');
        $productEs->setDateFieldName('updated');
        $productEs->setDataProcessing(DateTimeFormat::formatFromString());
        $productEs->setFilterFieldName('document_type="'.addcslashes(ProductInterface::class,'\\').'"');

        return [
            'es' => [$productEs]
        ];

    }

    private function checkIndexExists(string $index): bool
    {
        $indexParams['index'] = $index;
        if(!$this->esClient->indices()->exists($indexParams)){
            return false;
        }
        return true;
    }
}
