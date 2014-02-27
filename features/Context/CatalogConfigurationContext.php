<?php

namespace Context;

use Doctrine\Common\DataFixtures\ReferenceRepository;
use Behat\MinkExtension\Context\RawMinkContext;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;

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
     * @var ReferenceRepository Fixture reference repository
     */
    protected $referenceRepository;

    /**
     * @var string Path of the entity loaders
     */
    protected $entityLoaderPath = 'Context\Loader';

    /**
     * @var array Entity loaders and corresponding files
     */
    protected $preEntityLoaders = array(
        'CurrencyLoader'       => 'currencies',
        'LocaleLoader'         => null,
    );

    /**
     * @var array Entity loaders and corresponding files
     */
    protected $postEntityLoaders = array(
        'UserLoader'           => 'users',
    );
    /**
     * @param string $catalog
     *
     * @throws ExpectationException If configuration is not found
     * @Given /^(?:a|an|the) "([^"]*)" catalog configuration$/
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

        $treatedFiles = array();
        foreach ($this->preEntityLoaders as $loaderName => $fileName) {
            $loader = sprintf('%s\%s', $this->entityLoaderPath, $loaderName);
            $file = $fileName !== null ? sprintf('%s/%s.yml', $directory, $fileName) : null;
            if ($file) {
                $treatedFiles[] = $file;
            }
            $this->runLoader($loader, $file);
        }

        $files = array_diff(glob($directory.'/*'), $treatedFiles);
        if (count($files)) {
            $this->getContainer()
                ->get('pim_installer.fixture_loader.multiple_loader')
                ->load(
                    $this->getEntityManager(),
                    $this->referenceRepository,
                    $files
                );
        }

        foreach ($this->postEntityLoaders as $loaderName => $fileName) {
            $loader = sprintf('%s\%s', $this->entityLoaderPath, $loaderName);
            $file = $fileName !== null ? sprintf('%s/%s.yml', $directory, $fileName) : null;
            if ($file) {
                $treatedFiles[] = $file;
            }
            $this->runLoader($loader, $file);
        }
    }

    /**
     * Initialize the reference repository
     */
    private function initializeReferenceRepository()
    {
        $this->referenceRepository = new ReferenceRepository($this->getEntityManager());
        $listener = new ORMReferenceListener($this->referenceRepository);
        $this->getEntityManager()->getEventManager()->addEventSubscriber($listener);
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
        if ($filePath !== null) {
            $loader->setFilePath($filePath);
        }
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
