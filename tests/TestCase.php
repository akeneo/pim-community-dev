<?php

namespace Test\Integration;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Doctrine\Common\DataFixtures\Purger\MongoDBPurger;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TestCase extends WebTestCase
{
    /** @var int Count of test inside the same test class */
    protected static $count = 0;

    /** @var ContainerInterface */
    protected $container;

    /** @var string */
    protected $catalogName = 'technical';

    /** @var string */
    protected $extraDirectories = [];

    /** @var bool If you don't need to purge database between each test in the same test class, set to false */
    protected $purgeDatabaseForEachTest = true;

    /** @var string */
    protected $catalogDirectory;

    /** @var string */
    protected $fixturesDirectory;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        self::$count = 0;
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        static::bootKernel();

        $this->container = static::$kernel->getContainer();

        $projectRoot = $this->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;

        $this->catalogDirectory = $projectRoot.'tests'.DIRECTORY_SEPARATOR.'catalog'.DIRECTORY_SEPARATOR;
        $this->fixturesDirectory = $projectRoot.'tests'.DIRECTORY_SEPARATOR.'fixtures'.DIRECTORY_SEPARATOR;

        self::$count++;

        if ($this->purgeDatabaseForEachTest || 1 === self::$count) {
            $this->purgeDatabase();

            $files = $this->getConfigurationFiles();
            $this->loadCatalog($files);
        }
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function get($service)
    {
        return $this->container->get($service);
    }

    /**
     * @param string $service
     *
     * @return mixed
     */
    protected function getParameter($service)
    {
        return $this->container->getParameter($service);
    }

    /**
     * @param string[] $files Catalog configuration files to load
     *
     * @throws \Exception
     */
    protected function loadCatalog($files)
    {
        // prepare replace paths to use catalog paths and not the minimal fixtures path, please note that we can
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
        $jobLoader = $this->get('pim_installer.fixture_loader.job_loader');
        $jobLoader->loadJobInstances($replacePaths);

        // setup application to be able to run akeneo:batch:job command
        $application = new Application();
        $application->add(new BatchCommand());
        $batchJobCommand = $application->find('akeneo:batch:job');
        $batchJobCommand->setContainer($this->container);
        $command = new CommandTester($batchJobCommand);

        // install the catalog via the job instances
        $jobInstances = $jobLoader->getLoadedJobInstances();
        foreach ($jobInstances as $jobInstance) {
            $exitCode = $command->execute(
                [
                    'command'  => $batchJobCommand->getName(),
                    'code'     => $jobInstance->getCode(),
                    '--no-log' => true,
                    '-v'       => true
                ]
            );

            if (0 !== $exitCode) {
                throw new \Exception(sprintf('Catalog not installable! "%s"', $command->getDisplay()));
            }
        }

        $jobLoader->deleteJobInstances();
    }

    /**
     * Get the list of catalog configuration file paths to load
     *
     * @throws \InvalidArgumentException If configuration is not found
     *
     * @return string[]
     */
    protected function getConfigurationFiles()
    {
        $directories = array_merge([$this->catalogDirectory], $this->extraDirectories);

        $files = [];
        foreach ($directories as &$directory) {
            $directory .= DIRECTORY_SEPARATOR.$this->catalogName;
            $files     = array_merge($files, glob($directory.'/*'));
        }

        if (empty($files)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'No configuration found for catalog "%s", looked in "%s"',
                    $this->catalogName,
                    implode(', ', $directories)
                )
            );
        }

        return $files;
    }

    protected function purgeDatabase()
    {
        if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM === $this->getParameter('pim_catalog_product_storage_driver')) {
            $purgers[] = new MongoDBPurger($this->get('doctrine_mongodb')->getManager());
        }

        $purgers[] = new ORMPurger($this->get('doctrine')->getManager());

        foreach ($purgers as $purger) {
            $purger->purge();
        }
    }
}
