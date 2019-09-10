<?php

namespace AkeneoTest\Pim\Enrichment\Integration\Elasticsearch\IndexConfiguration;

use Akeneo\Test\Integration\TestCase;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;

/**
 * For each integration tests implemented in the subclass, this abstract:
 * - Resets the index (eg, removes the index configuration from ES and the documents indexed)
 * - It also provides function to make ES queries to that index and make sure the expected index are part of the result.
 *
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractPimCatalogTestCase extends TestCase
{
    private const PAGE_SIZE = 100;

    /** @var Client */
    protected $esProductClient;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return $this->catalog->useMinimalCatalog();
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');
        $this->addDocuments();
    }

    /**
     * This method indexes dummy products in elastic search.
     */
    abstract protected function addDocuments();

    /**
     * Indexes the given list of products
     *
     * @param array $products
     */
    protected function indexDocuments(array $products)
    {
        foreach ($products as $product) {
            $this->esProductClient->index($product['identifier'], $product);
        }

        $this->esProductClient->refreshIndex();
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

        $query['size'] = self::PAGE_SIZE;
        $response = $this->esProductClient->search($query);

        foreach ($response['hits']['hits'] as $hit) {
            $identifiers[] = $hit['_source']['identifier'];
        }

        return $identifiers;
    }

    /**
     * Checks that the products found are effectively expected
     *
     * @param array $actualProductIdentifiers
     * @param array $expectedProductIdentifiers
     */
    protected function assertDocument(array $actualProductIdentifiers, array $expectedProductIdentifiers)
    {
        sort($actualProductIdentifiers);
        sort($expectedProductIdentifiers);

        $this->assertSame($expectedProductIdentifiers, $actualProductIdentifiers);
    }
}
