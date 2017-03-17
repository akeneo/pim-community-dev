<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * For each integration tests implemented in the subclass, this abstract:
 * - Resets the index (eg, removes the index configuration from ES and the documents indexed)
 * - It also provides function to make ES queries to that index and make sure the expected index are part of the result.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractPimCatalogIntegration extends TestCase
{
    const DOCUMENT_TYPE = 'pim_catalog_product';

    /** @var Client */
    private $esClient;

    /** @var Loader */
    private $esConfigurationLoader;

    public function __construct($name = null, array $data = [], $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
    }

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getMinimalCatalogPath()], false);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->esClient = $this->get('akeneo_elasticsearch.client');
        $this->esConfigurationLoader = $this->get('akeneo_elasticsearch.index_configuration.loader');

        $this->resetIndex();
        $this->addProducts();
    }

    /**
     * Resets the index used for the integration tests query
     */
    private function resetIndex()
    {
        $conf = $this->esConfigurationLoader->load();

        try {
            $this->esClient->deleteIndex();
        } catch (\Exception $e) {
            // maybe the index does not exist, and we have nothing to do
            // TODO: handle that properly in the client, or add a hasIndex() method
        }

        $this->esClient->createIndex($conf->getAggregated());
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    abstract protected function addProducts();

    /**
     * Indexes the given list of products
     *
     * @param array $products
     */
    protected function indexProducts(array $products)
    {
        foreach ($products as $product) {
            $this->esClient->index(self::DOCUMENT_TYPE, $product['identifier'], $product);
        }

        // The indexation in Elasticsearch is an asynchronous process.
        // And even if this process is Near Real-Time, (see
        // https://www.elastic.co/guide/en/elasticsearch/guide/master/near-real-time.html#near-real-time)
        // for more information), we have to wait the indexation is
        // finished so that our tests are green.
        // Here, 5s is really more than enough according to the low number
        // of products we have to index.
        sleep(5);
    }

    /**
     * Executes the given query and returns the list of skus found.
     *
     * @param array $query
     *
     * @return array
     */
    protected function getSearchQueryResults(array $query)
    {
        $identifiers = [];
        $response = $this->esClient->search(self::DOCUMENT_TYPE, $query);

        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = $hit['_source']['identifier'];
        }

        return $identifiers;
    }

    /**
     * Checks that the products found are effectively expected
     *
     * @param array $productsFound
     * @param array $expectedProducts
     */
    protected function assertProducts(array $productsFound, array $expectedProducts)
    {
        $this->assertCount(count($expectedProducts), $productsFound);
        foreach ($expectedProducts as $productExpected) {
            $this->assertContains($productExpected, $productsFound);
        }
    }
}
