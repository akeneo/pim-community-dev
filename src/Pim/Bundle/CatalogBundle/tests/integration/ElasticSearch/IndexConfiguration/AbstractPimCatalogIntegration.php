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

        if ($this->esClient->hasIndex()) {
            $this->esClient->deleteIndex();
        }

        $this->esClient->createIndex($conf->buildAggregated());
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

        $this->esClient->refreshIndex();
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
     * @param array $productIdsFound
     * @param array $expectedProductIds
     */
    protected function assertProducts(array $productIdsFound, array $expectedProductIds)
    {
        sort($productIdsFound);
        sort($expectedProductIds);

        $this->assertSame($productIdsFound, $expectedProductIds);
    }
}
