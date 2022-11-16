<?php

namespace Akeneo\Tool\Bundle\DatabaseMetadataBundle\tests\integration;

use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
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

    private array $hosts;

    public function setUp(): void
    {
        parent::setUp();

        $clientBuilder = new ClientBuilder();
        $hosts = $_ENV['APP_INDEX_HOSTS'];
        $this->hosts = is_string($hosts) ? [$hosts] : $hosts; //all indexes ES
        $this->esClient = $clientBuilder->setHosts($this->hosts)->build();
        $this->loadData();
        $this->searchEs = new GenericEntityESIndexFinder($this->esClient);
    }

    /**
     * @dataProvider configProviderFilter
     */
    public function test_request_filter_with_order_by(EntityIndexConfiguration $entityIndexConfiguration): void
    {
        $fixtures = [
            ['product_' . $this->findProductUuidFromId(1), null],
            ['product_' . $this->findProductUuidFromId(2), null],
            ['product_' . $this->findProductUuidFromId(3), null],
            ['product_' . $this->findProductUuidFromId(4), null],
        ];
        \usort($fixtures, static fn (array $array1, array $array2): int => \strcmp($array1[0], $array2[0]));
        $tests = new \ArrayIterator($fixtures);
        $resultsFormat = [];
        foreach ($tests as $test) {
            $resultsFormat[] = IndexResultsFactory::initIndexFormatDataResults($test[0], $test[1]);
        }
        $resultsFixtures = new \ArrayIterator($resultsFormat);

        $results = $this->searchEs->findAllByOrder($entityIndexConfiguration);
        for ($i = 0; $i < 4; $i++) {
            $resultsOrderQueryFormat[] = IndexResultsFactory::initIndexFormatDataResults($results[$i]["identifier"], null);
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
        $productEs->setFilterFieldName(sprintf('document_type="%s"',addcslashes(ProductInterface::class, '\\')));

        return [
            'es' => [$productEs]
        ];
    }

    public function resetIndex(string $str): void
    {
        $clientProduct = $this->getContainer()->get($str);
        $clientProduct->resetIndex();
    }

    protected function getConfiguration(): Configuration
    {
        return $this->catalog->useMinimalCatalog();
    }

    private function loadData(): void
    {
        $product0 = $this->get('pim_catalog.builder.product')->createProduct('product_model_1');
        $product1 = $this->get('pim_catalog.builder.product')->createProduct('product_model_2');
        $product2 = $this->get('pim_catalog.builder.product')->createProduct('product_model_3');
        $product3 = $this->get('pim_catalog.builder.product')->createProduct('product_model_4');
        $productArray = [$product0,$product1,$product2, $product3];
        foreach($productArray as $product){
            $this->get('pim_catalog.updater.product')->update($product, []);
            $this->get('pim_catalog.saver.product')->save($product);
        }

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
    }

    private function findProductUuidFromId(int $productId): string
    {
        $uuid = $this->get('database_connection')->executeQuery(
            'SELECT BIN_TO_UUID(uuid) FROM pim_catalog_product WHERE id = ?',
            [$productId]
        )->fetchOne();

        return $uuid;
    }
}
