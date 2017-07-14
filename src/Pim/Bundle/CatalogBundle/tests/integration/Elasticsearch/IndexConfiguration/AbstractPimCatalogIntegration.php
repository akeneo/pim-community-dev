<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\Elasticsearch\IndexConfiguration;

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
    const PRODUCT_MODEL_DOCUMENT_TYPE = 'pim_catalog_product_model_parent_';

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration([Configuration::getMinimalCatalogPath()]);
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->esClient = $this->get('akeneo_elasticsearch.client');
        $this->esConfigurationLoader = $this->get('akeneo_elasticsearch.index_configuration.loader');

        $this->addProducts();
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
            $this->esClient->index(
                self::DOCUMENT_TYPE,
                $product['identifier'],
                $product,
                $product['parent'],
                $product['root_ancestor']
            );
        }

        $this->esClient->refreshIndex();
    }

    protected function indexProductModels(array $productModels)
    {
        foreach ($productModels as $productModel) {
            $parent = null;
            $rootAncestor = null;

            if (isset($productModel['parent'])) {
                $parent = $productModel['parent'];
            }
            if (isset($productModel['root_ancestor'])) {
                $rootAncestor = $productModel['root_ancestor'];
            }

            $this->esClient->index(
                self::PRODUCT_MODEL_DOCUMENT_TYPE . $productModel['level'],
                $productModel['identifier'],
                $productModel,
                $parent,
                $rootAncestor
            );
        }

        $this->esClient->refreshIndex();
    }

    /**
     * Executes the given query and returns the list of skus found.
     *
     * @param array $query
     * @param array $types
     *
     * @return array
     */
    protected function getSearchQueryResults(array $query, array $types = [])
    {
        $identifiers = [];
        $types = self::DOCUMENT_TYPE . ',' . join(',', $types);

        $response = $this->esClient->search($types, $query);

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
    protected function assertProducts(array $actualProductIdentifiers, array $expectedProductIdentifiers)
    {
        sort($actualProductIdentifiers);
        sort($expectedProductIdentifiers);

        $this->assertSame($actualProductIdentifiers, $expectedProductIdentifiers);
    }
}
