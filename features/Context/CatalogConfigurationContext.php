<?php

namespace Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Doctrine\Common\DataFixtures\ReferenceRepository;

/**
 * A context for initializing catalog configuration
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogConfigurationContext extends RawMinkContext
{
    /**
     * @var string Catalog configuration path
     */
    protected $catalogPath = 'catalog';

    /**
     * Fixture reference repository
     * @var ReferenceRepository
     */
    protected $referenceRepository;

    /**
     * @var string Path of the entity loaders
     */
    protected $entityLoaderPath = 'Context\Loader';

    /**
     * @var array Entity loaders and corresponding files
     */
    protected $entityLoaders = array(
        'AttributeGroupLoader' => 'attribute_groups',
        'AttributeLoader'      => 'attributes',
    );

    /**
     * Initialize the reference repository
     */
    private function initializeReferenceRepository()
    {
        $this->referenceRepository = new ReferenceRepository($this->getEntityManager());
    }

    /**
     * @param string $catalog
     *
     * @throws ExpectationException If configuration is not found
     * @Given /^a "([^"]*)" catalog configuration$/
     */
    public function aCatalogConfiguration($catalog)
    {
        $directory = sprintf('%s/%s/%s', __DIR__, $this->catalogPath, strtolower($catalog));

        if (!file_exists($directory)) {
            throw $this->getMainContext()->createExpectationException(
                sprintf('No configuration found for catalog "%s", looked in "%s"', $catalog, $directory)
            );
        }

        $this->createCatalog($directory);
    }

    /**
     * @param string $directory
     */
    private function createCatalog($directory)
    {
        $this->initializeReferenceRepository();

        foreach ($this->entityLoaders as $loaderName => $fileName) {
            $loader = sprintf('%s\%s', $this->entityLoaderPath, $loaderName);
            $file = sprintf('%s/%s.yml', $directory, $fileName);
            $this->runLoader($loader, $file);
        }
    }

    /**
     * Run an entity loader
     * @param string $loaderClass
     * @param string $filePath
     */
    private function runLoader($loaderClass, $filePath)
    {
        $loader = new $loaderClass();
        $loader->setContainer($this->getContainer());
        $loader->setReferenceRepository($this->referenceRepository);
        $loader->setFilePath($filePath);
        $loader->load($this->getEntityManager());
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    private function getEntityManager()
    {
        return $this->getMainContext()->getEntityManager();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }
}
