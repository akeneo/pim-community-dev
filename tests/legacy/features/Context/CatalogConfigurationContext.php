<?php

namespace Context;

use Context\Loader\FixturesLoader;
use Pim\Behat\Context\PimContext;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Tester\CommandTester;
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
    protected $fixturesLoader;

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
                $this->getContainer()->get('akeneo_elasticsearch.client.product_and_product_model')->resetIndex();
                $this->getContainer()->get('akeneo_elasticsearch.client.product_and_product_model')->refreshIndex();
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
     * @param string[] $files Catalog configuration files to load
     *
     * @throws \Exception
     */
    protected function loadCatalog($files)
    {
        // prepare replace paths to use Behat catalog paths and not the minimal fixtures path, please note that we can
        // have several files per job in case of Enterprise Catalog, for instance,
        // [
        //     'jobs' => [
        //         "/project/features/Context/catalog/footwear/jobs.yml"
        //         "/project/features/PimEnterprise/Behat/Context/../../../Context/catalog/footwear/jobs.yml"
        // ]
        $replacePaths = [];
        foreach ($files as $file) {
            $tokens = explode(DIRECTORY_SEPARATOR, $file);
            $fileName = array_pop($tokens);
            if (!isset($replacePaths[$fileName])) {
                $replacePaths[$fileName] = [];
            }
            $replacePaths[$fileName][] = $file;
        }

        // configure and load job instances in database
        $this->getFixtureJobLoader()->loadJobInstances($replacePaths);

        // setup application to be able to run akeneo:batch:job command
        $application = new Application($this->getContainer()->get('kernel'));
        $application->setAutoExit(false);

        // install the catalog via the job instances
        $jobInstances = $this->getFixtureJobLoader()->getLoadedJobInstances();
        foreach ($jobInstances as $jobInstance) {
            $input = new ArrayInput([
                'command'  => 'akeneo:batch:job',
                'code'     => $jobInstance->getCode(),
                '--no-log' => true,
                '-v'       => true
            ]);
            $output = new BufferedOutput();
            $exitCode = $application->run($input, $output);

            if (0 !== $exitCode) {
                throw new \Exception(sprintf('Catalog not installable! "%s"', $output->fetch()));
            }
        }

        // delete the job instances
        $this->getFixtureJobLoader()->deleteJobInstances();

        // install reference data
        $bundles = $this->getContainer()->getParameter('kernel.bundles');
        if (isset($bundles['AcmeAppBundle'])) {
            $referenceDataLoader = new ReferenceDataLoader();
            $referenceDataLoader->load($this->getEntityManager());
        }

        $this->getElasticsearchProductClient()->refreshIndex();
    }

    /**
     * @return FixtureJobLoader
     */
    protected function getFixtureJobLoader()
    {
        return $this->getContainer()->get('pim_installer.fixture_loader.job_loader');
    }

    /**
     * @return Client
     */
    protected function getElasticsearchProductClient()
    {
        return $this->getContainer()->get('akeneo_elasticsearch.client.product_and_product_model');
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
