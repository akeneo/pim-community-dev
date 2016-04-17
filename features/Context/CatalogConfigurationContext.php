<?php

namespace Context;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Behat\Mink\Exception\ExpectationException;
use Behat\MinkExtension\Context\RawMinkContext;
use Context\Loader\ReferenceDataLoader;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Pim\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\CS\Console\Application;

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
        // load JobInstances
        $this->getFixtureJobLoader()->load($files);

        // setup akeneo:batch:job command
        $application = new Application();
        $application->add(new BatchCommand());
        $batchJobCommand = $application->find('akeneo:batch:job');
        $batchJobCommand->setContainer($this->getContainer());
        $commandTester = new CommandTester($batchJobCommand);

        // install the catalog
        $jobInstances = $this->getFixtureJobLoader()->getRunnableJobInstances();
        foreach ($jobInstances as $jobInstance) {
            $commandTester->execute(
                [
                    'command'    => $batchJobCommand->getName(),
                    'code'       => $jobInstance->getCode(),
                    '--no-log'   => true,
                    '-v'         => true
                ]
            );
        }

        // delete the job instances
        $this->getFixtureJobLoader()->deleteJobs();

        // install reference data
        $bundles = $this->getContainer()->getParameter('kernel.bundles');
        if (isset($bundles['AcmeAppBundle'])) {
            $referenceDataLoader = new ReferenceDataLoader();
            $referenceDataLoader->load($this->getEntityManager());
        }
    }

    /**
     * @return FixtureJobLoader
     */
    protected function getFixtureJobLoader()
    {
        return $this->getContainer()->get('pim_installer.fixture_loader.job_loader');
    }

    /**
     * Get the list of catalog configuration file paths to load
     *
     * @param string $catalog
     *
     * @throws ExpectationException If configuration is not found
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
     * Initialize the reference repository
     */
    protected function initializeReferenceRepository()
    {
        $this->referenceRepository = new ReferenceRepository($this->getEntityManager());
        $listener                  = new ORMReferenceListener($this->referenceRepository);
        $this->getEntityManager()->getEventManager()->addEventSubscriber($listener);
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
