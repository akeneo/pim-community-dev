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
     * @var array Additional catalog configuration directories
     */
    protected $extraDirectories = [];

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
        $this->initializeReferenceRepository();

        $this->loadCatalog($this->getConfigurationFiles($catalog));
    }

    /**
     * @param string[] $files Catalog configuration files to load
     */
    protected function loadCatalog($files)
    {
        $treatedFiles = [];
        foreach ($this->preEntityLoaders as $loaderName => $fileName) {
            $loader = sprintf('%s\%s', $this->entityLoaderPath, $loaderName);
            $file = $this->getLoaderFile($files, $fileName);
            if ($file) {
                $treatedFiles[] = $file;
            }
            $this->runLoader($loader, $file);
        }

        $files = array_diff($files, $treatedFiles);
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
            $file = $this->getLoaderFile($files, $fileName);
            if ($file) {
                $treatedFiles[] = $file;
            }
            $this->runLoader($loader, $file);
        }
    }

    /**
     * Get the list of catalog configuration file paths to load
     *
     * @param string $catalog
     *
     * @return string[]
     *
     * @throws ExpectationException If configuration is not found
     */
    protected function getConfigurationFiles($catalog)
    {
        $directories = array_merge([__DIR__.'/'.$this->catalogPath], $this->extraDirectories);

        $files = [];
        foreach ($directories as &$directory) {
            $directory = sprintf('%s/%s', $directory, strtolower($catalog));
            $files = array_merge($files, glob($directory.'/*'));
        }

        if (empty($files)) {
            throw $this->getMainContext()->createExpectationException(
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
     * Find the appropriate file for the loader in the catalog configuration files
     *
     * @param string[]    $files
     * @param string|null $fileName
     *
     * @return string|null
     *
     * @throws ExpectationException If the requested file is not found
     */
    protected function getLoaderFile($files, $fileName)
    {
        if ($fileName !== null) {
            $matchingFiles = array_filter(
                $files,
                function ($file) use ($fileName) {
                    return $fileName === pathinfo($file, PATHINFO_FILENAME);
                }
            );

            if (empty($matchingFiles)) {
                throw $this->getMainContext()->createExpectationException(
                    sprintf('Catalog configuration file "%s" not found', $fileName)
                );
            }

            return end($matchingFiles);
        }
    }

    /**
     * Initialize the reference repository
     */
    protected function initializeReferenceRepository()
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
    protected function runLoader($loaderClass, $filePath)
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
    protected function getEntityManager()
    {
        return $this->getMainContext()->getEntityManager();
    }

    /**
     * @return \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }
}
