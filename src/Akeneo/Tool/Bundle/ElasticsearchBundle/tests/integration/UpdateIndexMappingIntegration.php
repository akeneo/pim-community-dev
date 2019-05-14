<?php
declare(strict_types=1);

namespace src\Akeneo\Bundle\ElasticsearchBundle\tests\integration;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\UpdateIndexMapping;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Elasticsearch\ClientBuilder;
use PHPUnit\Framework\Assert;
use Pim\Bundle\CatalogBundle\Elasticsearch\ProductQueryBuilderFactory;
use Pim\Component\Catalog\Model\ProductInterface;

class UpdateIndexMappingIntegration extends TestCase
{
    public function test_it_moves_the_index_and_the_pqb_still_works()
    {
        $indexHost = $this->getParameter('index_hosts');
        /** @var Client $akeneoProductClient */
        $akeneoProductClient = $this->get('akeneo_elasticsearch.client.product');
        
        $clientBuilder = new ClientBuilder();
        $clientBuilder->setHosts(is_string($indexHost) ? [$indexHost] : $indexHost);

        $client = $clientBuilder->build();

        $indices = array_map(function (array $index) : string {
            return $index['index'];
        }, $client->cat()->indices());

        /** @var ProductQueryBuilderFactory $pqb */
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory');
        $this->createProduct('product1');
        Assert::assertEquals(1, $pqb->create()->execute()->count());
        Assert::assertContains($this->getParameter('product_index_name'), $indices);

        $updateIndexMapping = new UpdateIndexMapping();
        $updateIndexMapping->updateIndexMapping($client, $akeneoProductClient->getIndexName(), $akeneoProductClient->getConfigurationLoader());

        $newIndices = array_map(function (array $index) : string {
            return $index['index'];
        }, $client->cat()->indices());

        Assert::assertNotContains($this->getParameter('product_index_name'), $newIndices);
        Assert::assertTrue($client->indices()->existsAlias(['name' => $this->getParameter('product_index_name')]));
        Assert::assertEquals(1, $pqb->create()->execute()->count());
    }

    /**
     * @param string $identifier
     * @param array  $data
     *
     * @return ProductInterface
     */
    protected function createProduct(string $identifier, array $data = []): ProductInterface
    {
        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->get('akeneo_elasticsearch.client.product')->refreshIndex();

        return $product;
    }

    /**
     * @return Configuration
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }
}
