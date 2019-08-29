<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\ElasticsearchBundle\tests\integration;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\ProductQueryBuilderFactory;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\IndexConfiguration\UpdateIndexMapping;
use Elasticsearch\ClientBuilder;
use PHPUnit\Framework\Assert;

class UpdateIndexMappingIntegration extends TestCase
{
    public function test_it_moves_the_index_and_the_pqb_still_works()
    {
        $indexHost = $this->getParameter('index_hosts');
        /** @var Client $akeneoProductClient */
        $akeneoProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');

        $clientBuilder = new ClientBuilder();
        $clientBuilder->setHosts(is_string($indexHost) ? [$indexHost] : $indexHost);

        $client = $clientBuilder->build();

        $aliases = array_map(function (array $index) : string {
            return $index['alias'];
        }, $client->cat()->aliases());

        /** @var ProductQueryBuilderFactory $pqb */
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory');
        $this->createProduct('product1');
        Assert::assertEquals(1, $pqb->create()->execute()->count());
        Assert::assertContains($this->getParameter('product_and_product_model_index_name'), $aliases);

        $updateIndexMapping = new UpdateIndexMapping();
        $updateIndexMapping->updateIndexMapping($client, $akeneoProductClient->getIndexName(), $akeneoProductClient->getConfigurationLoader());

        $newIndices = array_map(function (array $index) : string {
            return $index['index'];
        }, $client->cat()->indices());

        Assert::assertNotContains($this->getParameter('product_and_product_model_index_name'), $newIndices);
        Assert::assertTrue($client->indices()->existsAlias(['name' => $this->getParameter('product_and_product_model_index_name')]));
        Assert::assertEquals(1, $pqb->create()->execute()->count());
    }

    public function test_it_is_idempotent()
    {
        $indexHost = $this->getParameter('index_hosts');
        /** @var Client $akeneoProductClient */
        $akeneoProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');

        $clientBuilder = new ClientBuilder();
        $clientBuilder->setHosts(is_string($indexHost) ? [$indexHost] : $indexHost);

        $client = $clientBuilder->build();

        $aliases = array_map(function (array $index) : string {
            return $index['alias'];
        }, $client->cat()->aliases());

        /** @var ProductQueryBuilderFactory $pqb */
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory');
        $this->createProduct('product1');
        Assert::assertEquals(1, $pqb->create()->execute()->count());
        Assert::assertContains($this->getParameter('product_and_product_model_index_name'), $aliases);


        $updateIndexMapping = new UpdateIndexMapping();
        // from alias to alias
        $updateIndexMapping->updateIndexMapping($client, $akeneoProductClient->getIndexName(), $akeneoProductClient->getConfigurationLoader());
        // from alias to alias
        $updateIndexMapping->updateIndexMapping($client, $akeneoProductClient->getIndexName(), $akeneoProductClient->getConfigurationLoader());
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

        $this->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();

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
