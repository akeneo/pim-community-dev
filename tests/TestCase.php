<?php

namespace Akeneo\Test\Integration;

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
abstract class TestCase extends WebTestCase
{
    /** @var int Count of test inside the same test class */
    protected static $count = 0;

    /** @var ContainerInterface */
    protected $container;

    /**
     * {@inheritdoc}
     */
    public static function setUpBeforeClass()
    {
        self::$count = 0;
    }

    /**
     * @return Configuration
     */
    abstract protected function getConfiguration();

    /**
     * Method executed after the fixture import
     */
    protected function doAfterFixtureImport(Application $application)
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        static::bootKernel();

        $this->container = static::$kernel->getContainer();

        $configuration = $this->getConfiguration();
        self::$count++;

        if ($configuration->isDatabasePurgedForEachTest() || 1 === self::$count) {
            $this->purgeDatabase();

            $files = $this->getFilesToLoad($configuration->getCatalogDirectories());
            $filesByType = $this->getFilesToLoadByType($files);
            $this->loadSqlFiles($filesByType['sql']);
            $this->loadMongoDbFiles($filesByType['mongodb']);
            $this->loadImportFiles($filesByType['import']);
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
     * @return string
     */
    public function getRootPath()
    {
        return realpath($this->getParameter('kernel.root_dir').DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
    }

    /**
     * Load SQL file directly by a regular SQL query
     *
     * @param array $files
     */
    protected function loadSqlFiles(array $files)
    {
        $db = $this->get('doctrine.orm.entity_manager')->getConnection();

        foreach ($files as $file) {
            $db->executeQuery(file_get_contents($file));
        }
    }

    /**
     * Load Mongo files directly by a regular MongoDB query
     *
     * @param array $files
     */
    protected function loadMongoDbFiles(array $files)
    {
        $storage = $this->container->getParameter('pim_catalog_product_storage_driver');
        if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM !== $storage) {
            return;
        }

        $db = $this->get('doctrine.odm.mongodb.document_manager')->getConnection()->akeneo_pim;
        foreach ($files as $file) {
            $db->execute(file_get_contents($file));
        }
    }

    /**
     * Load import files via akeneo:batch:job
     *
     * @param array $files
     *
     * @throws \Exception
     */
    protected function loadImportFiles(array $files)
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

        $this->doAfterFixtureImport($application);

        // close the connection created specifically for this repository
        // TODO: to remove when TIP-385 will be done
        $doctrineJobRepository = $this->get('akeneo_batch.job_repository');
        $doctrineJobRepository->getJobManager()->getConnection()->close();
    }

    /**
     * Separate files to load by their type. They can be:
     *  - regular files to load from an import.
     *  - SQL files
     *  - mongo files
     *
     * @param array $files
     *
     * @return array
     */
    protected function getFilesToLoadByType(array $files)
    {
        $filesByType = [
            'sql' => [],
            'mongodb' => [],
            'import' => [],
        ];

        foreach ($files as $filePath) {
            $realPathParts = pathinfo($filePath);

            // do not try to load files without extension
            if (!isset($realPathParts['extension'])) {
                continue;
            }

            // do not try to load product file that do not match the storage
            if ('200_products' === $realPathParts['filename']) {
                $storage = $this->container->getParameter('pim_catalog_product_storage_driver');
                if ($storage === AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM &&
                    'sql' === $realPathParts['extension']
                ) {
                    continue;
                }
                elseif ($storage === AkeneoStorageUtilsExtension::DOCTRINE_ORM &&
                    'json' === $realPathParts['extension']
                ) {
                    continue;
                }
            }

            switch ($realPathParts['extension']) {
                case 'json':
                    $filesByType['mongodb'][] = $filePath;
                    break;
                case 'sql':
                    $filesByType['sql'][] = $filePath;
                    break;
                case 'csv':
                case 'xls':
                case 'xlsx':
                case 'yml':
                case 'yaml':
                    $filesByType['import'][] = $filePath;
                    break;
                default:
                    break;
            }
        }

        return $filesByType;
    }

    /**
     * Get the list of catalog configuration file paths to load
     *
     * @param array $directories
     *
     * @return array
     * @throws \Exception if no files can be loaded
     *
     */
    protected function getFilesToLoad(array $directories)
    {
        $rawFiles = [];
        foreach ($directories as $directory) {
            $rawFiles = array_merge($rawFiles, glob($directory.'/*'));
        }

        if (empty($rawFiles)) {
            throw new \Exception(
                sprintf(
                    'No catalog file to load found in "%s"',
                    implode(', ', $directories)
                )
            );
        }

        $files = [];
        foreach ($rawFiles as $rawFilePath) {
            if (false === $realFilePath = realpath($rawFilePath)) {
                continue;
            }
            $files[] = $rawFilePath;
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

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        if (AkeneoStorageUtilsExtension::DOCTRINE_MONGODB_ODM === $this->getParameter('pim_catalog_product_storage_driver')) {
            $doctrine = $this->get('doctrine_mongodb');
        } else {
            $doctrine = $this->get('doctrine');
        }

        foreach ($doctrine->getConnections() as $connection) {
            $connection->close();
        }

        parent::tearDown();
    }

    /**
     * Look in every fixture directory if a fixture $name exists.
     * And return the pathname of the fixture if it exists.
     *
     * @param string $name
     *
     * @return string
     *
     * @throws \Exception if no fixture $name has been found
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
