<?php

namespace Akeneo\Test\Integration;

use Akeneo\Bundle\ElasticsearchBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
abstract class TestCase extends KernelTestCase
{
    /** @var Client */
    protected $esProductClient;

    /** @var Client */
    protected $esProductAndProductModelClient;

    /**
     * @return Configuration
     */
    abstract protected function getConfiguration();

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        static::bootKernel(['debug' => false]);

        $configuration = $this->getConfiguration();

        $this->esProductClient = $this->get('akeneo_elasticsearch.client.product');
        $this->esProductAndProductModelClient = $this->get('akeneo_elasticsearch.client.product_and_product_model');

        $databaseSchemaHandler = $this->getDatabaseSchemaHandler();

        $fixturesLoader = $this->getFixturesLoader($configuration, $databaseSchemaHandler);
        $fixturesLoader->load();

        $this->esProductClient->resetIndex();
        $this->esProductAndProductModelClient->resetIndex();

        $products = $this->get('pim_catalog.repository.product')->findAll();
        $this->get('pim_catalog.elasticsearch.indexer.product')->indexAll($products);

        // TODO: Reindex all product models and their children (see PIM-6646)
        // for instance:
        // $models = $this->get('pim_catalog.repository.product')->findAll();
        // $this->get('pim_catalog.elasticsearch.product_model_indexer')->indexAll($models);
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function get($service)
    {
        return static::$kernel->getContainer()->get($service);
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function getParameter($service)
    {
        return static::$kernel->getContainer()->getParameter($service);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        $connectionCloser = $this->getConnectionCloser();
        $connectionCloser->closeConnections();

        parent::tearDown();
    }

    /**
     * @return DatabaseSchemaHandler
     */
    protected function getDatabaseSchemaHandler()
    {
        return new DatabaseSchemaHandler(static::$kernel);
    }

    /**
     * @param Configuration         $configuration
     * @param DatabaseSchemaHandler $databaseSchemaHandler
     *
     * @return FixturesLoader
     */
    protected function getFixturesLoader(Configuration $configuration, DatabaseSchemaHandler $databaseSchemaHandler)
    {
        return new FixturesLoader(static::$kernel, $configuration, $databaseSchemaHandler);
    }

    /**
     * @return ConnectionCloser
     */
    protected function getConnectionCloser()
    {
        return new ConnectionCloser(static::$kernel->getContainer());
    }

    /**
     * Look in every fixture directory if a fixture $name exists.
     * And return the pathname of the fixture if it exists.
     *
     * @param string $name
     *
     * @throws \Exception if no fixture $name has been found
     *
     * @return string
     */
    protected function getFixturePath($name)
    {
        $configuration = $this->getConfiguration();
        foreach ($configuration->getFixtureDirectories() as $fixtureDirectory) {
            $path = $fixtureDirectory . $name;
            if (is_file($path) && false !== realpath($path)) {
                return realpath($path);
            }
        }

        throw new \Exception(sprintf('The fixture "%s" does not exist.', $name));
    }
}
