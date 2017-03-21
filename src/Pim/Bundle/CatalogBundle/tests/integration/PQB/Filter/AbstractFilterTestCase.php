<?php

namespace Pim\Bundle\CatalogBundle\tests\integration\PQB\Filter;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Akeneo\Bundle\ElasticsearchBundle\IndexConfiguration\Loader;
use Akeneo\Component\StorageUtils\Cursor\CursorInterface;
use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\Integration\TestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class AbstractFilterTestCase extends TestCase
{
    /** @var Client */
    private $esClient;

    /** @var Loader */
    private $esConfigurationLoader;

    /**
     * {@inheritdoc}
     */
    protected function getConfiguration()
    {
        return new Configuration(
            [Configuration::getTechnicalCatalogPath()],
            false
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->esClient = $this->get('akeneo_elasticsearch.client');
        $this->esConfigurationLoader = $this->get('akeneo_elasticsearch.index_configuration.loader');
    }

    /**
     * @param string $identifier
     * @param array  $data
     */
    protected function createProduct($identifier, array $data)
    {
        $family = isset($data['family']) ? $data['family'] : null;

        $product = $this->get('pim_catalog.builder.product')->createProduct($identifier, $family);
        $this->get('pim_catalog.updater.product')->update($product, $data);
        $this->get('pim_catalog.saver.product')->save($product);

        $this->esClient->refreshIndex();
    }

    /**
     * @param array $data
     */
    protected function createAttribute(array $data)
    {
        $attribute = $this->get('pim_catalog.factory.attribute')->create();
        $this->get('pim_catalog.updater.attribute')->update($attribute, $data);
        $this->get('pim_catalog.saver.attribute')->save($attribute);
    }

    /**
     * @param array $data
     */
    protected function createAttributeOption(array $data)
    {
        $attributeOption = $this->get('pim_catalog.factory.attribute_option')->create();
        $this->get('pim_catalog.updater.attribute_option')->update($attributeOption, $data);
        $this->get('pim_catalog.saver.attribute_option')->save($attributeOption);
    }

    /**
     * @param array $filters
     *
     * @return mixed
     */
    protected function execute(array $filters)
    {
        $pqb = $this->get('pim_catalog.query.product_query_builder_factory')->create();

        foreach ($filters as $filter) {
            $context = isset($filter[3]) ? $filter[3] : [];
            $pqb->addFilter($filter[0], $filter[1], $filter[2], $context);
        }

        return $pqb->execute();
    }

    /**
     * @param CursorInterface $result
     * @param array           $expected
     */
    protected function assert($result, array $expected)
    {
        $products = [];
        foreach ($result as $product) {
            $products[] = $product->getIdentifier();
        }

        sort($products);
        sort($expected);

        $this->assertSame($products, $expected);
    }

    /**
     * Resets the index used for the integration tests
     */
    protected function resetIndex()
    {
        $conf = $this->esConfigurationLoader->load();

        if ($this->esClient->hasIndex()) {
            $this->esClient->deleteIndex();
        }

        $this->esClient->createIndex($conf->buildAggregated());
    }
}
