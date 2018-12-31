<?php

namespace Context;

use Context\Loader\FixturesLoader;
use Pim\Behat\Context\PimContext;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A context for initializing catalog configuration
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogConfigurationContext extends PimContext
{
    /** @var string Catalog configuration path */
    protected $catalogPath = 'catalog';

    /** @var array Additional catalog configuration directories */
    protected $extraDirectories = [];

    /** @var FixturesLoader */
    private $fixturesLoader;

    /**
     * Add an additional directory for catalog configuration files
     *
     * @param string $directory
     *
     * @return CatalogConfigurationContext
     */
    public function addConfigurationDirectory($directory)
    {
        $this->extraDirectories[] = $directory;

        return $this;
    }

    /**
     * @param string $catalog
     *
     * @Given /^(?:a|an|the) "([^"]*)" catalog configuration$/
     */
    public function aCatalogConfiguration($catalog)
    {
        if (null === $this->fixturesLoader) {
            $this->fixturesLoader = new FixturesLoader($this->getContainer());
        }

        $this->fixturesLoader->load($this->getConfigurationFiles($catalog));
    }

    /**
     * @param string $entity
     *
     * @Given /^there is no "([^"]*)" in the catalog$/
     */
    public function thereIsNoSuchEntityInTheCatalog($entity)
    {
        $db = $this->getMainContext()->getContainer()->get('doctrine.dbal.default_connection');

        switch ($entity) {
            case 'product':
                $db->exec('DELETE FROM pim_catalog_product');
                $this->getContainer()->get('akeneo_elasticsearch.client.product')->resetIndex();
                $this->getContainer()->get('akeneo_elasticsearch.client.product')->refreshIndex();
                break;
            case 'product model':
                $db->exec('DELETE FROM pim_catalog_product_model');
                $this->getContainer()->get('akeneo_elasticsearch.client.product_and_product_model')->resetIndex();
                $this->getContainer()->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
                break;
            default:
                throw new \InvalidArgumentException(
                    sprintf('The purge of "%s" in the catalog has not been implemented yet.')
                );
        }
    }

    /**
     * Get the list of catalog configuration file paths to load
     *
     * @param string $catalog
     *
     * @throws \InvalidArgumentException If configuration is not found
     *
     * @return string[]
     */
    protected function getConfigurationFiles($catalog)
    {
        $directories = array_merge([__DIR__.'/'.$this->catalogPath], $this->extraDirectories);

        $files = [];
        foreach ($directories as &$directory) {
            $directory = sprintf('%s/%s', $directory, strtolower($catalog));
            $files     = array_merge($files, glob($directory.'/*'));
        }

        if (empty($files)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'No configuration found for catalog "%s", looked in "%s"',
                    $catalog,
                    implode(', ', $directories)
                )
            );
        }

        return $files;
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }
}
