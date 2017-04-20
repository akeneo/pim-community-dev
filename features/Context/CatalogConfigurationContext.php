<?php

namespace Context;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Akeneo\Bundle\ElasticsearchBundle\Client;
use Behat\MinkExtension\Context\RawMinkContext;
use Context\Loader\ReferenceDataLoader;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * A context for initializing catalog configuration
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogConfigurationContext extends RawMinkContext
{
    /** @var string Catalog configuration path */
    protected $catalogPath = 'catalog';

    /** @var array Additional catalog configuration directories */
    protected $extraDirectories = [];

    /** @var ReferenceRepository Fixture reference repository */
    protected $referenceRepository;

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
        $application = new Application();
        $application->add(new BatchCommand());
        $batchJobCommand = $application->find('akeneo:batch:job');
        $batchJobCommand->setContainer($this->getContainer());
        $command = new CommandTester($batchJobCommand);

        // install the catalog via the job instances
        $jobInstances = $this->getFixtureJobLoader()->getLoadedJobInstances();
        foreach ($jobInstances as $jobInstance) {
            $exitCode = $command->execute(
                [
                    'command'    => $batchJobCommand->getName(),
                    'code'       => $jobInstance->getCode(),
                    '--no-log'   => true,
                    '-v'         => true
                ]
            );
            if (0 !== $exitCode) {
                throw new \Exception(sprintf('Catalog not installable! "%s"', $command->getDisplay()));
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

        $this->getElasticsearchClient()->refreshIndex();
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
    protected function getElasticsearchClient()
    {
        return $this->getContainer()->get('akeneo_elasticsearch.client');
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
     * Initialize the reference repository
     */
    protected function initializeReferenceRepository()
    {
        $this->referenceRepository = new ReferenceRepository($this->getEntityManager());
        $listener                  = new ORMReferenceListener($this->referenceRepository);
        $this->getEntityManager()->getEventManager()->addEventSubscriber($listener);
    }

    /**
     * @return EntityManagerInterface
     */
    protected function getEntityManager()
    {
        return $this->getMainContext()->getEntityManager();
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getMainContext()->getContainer();
    }
}
