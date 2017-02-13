<?php

namespace Akeneo\Test\Integration;

use Akeneo\Bundle\BatchBundle\Command\BatchCommand;
use Akeneo\Bundle\StorageUtilsBundle\DependencyInjection\AkeneoStorageUtilsExtension;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FixturesLoader
{
    /** @var ContainerInterface */
    protected $container;

    /** @var Configuration */
    protected $configuration;

    /**
     * @param ContainerInterface $container
     * @param Configuration      $configuration
     */
    public function __construct(ContainerInterface $container, Configuration $configuration)
    {
        $this->container = $container;
        $this->configuration = $configuration;
    }

    public function __destruct()
    {
        // close the connection created specifically for this repository
        // TODO: to remove when TIP-385 will be done
        $doctrineJobRepository = $this->container->get('akeneo_batch.job_repository');
        $doctrineJobRepository->getJobManager()->getConnection()->close();
    }

    /**
     * Loads test catalog.
     *
     * @throws \Exception
     */
    public function load()
    {
        $files = $this->getFilesToLoad($this->configuration->getCatalogDirectories());
        $filesByType = $this->getFilesToLoadByType($files);

        $this->loadSqlFiles($filesByType['sql']);
        $this->loadMongoDbFiles($filesByType['mongodb']);
        $this->loadImportFiles($filesByType['import']);
        $this->loadReferenceData();
    }

    /**
     * Load SQL file directly by a regular SQL query
     *
     * @param array $files
     */
    protected function loadSqlFiles(array $files)
    {
        $db = $this->container->get('doctrine.orm.entity_manager')->getConnection();

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

        $db = $this->container->get('doctrine.odm.mongodb.document_manager')->getConnection()->akeneo_pim;
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
        $jobLoader = $this->container->get('pim_installer.fixture_loader.job_loader');
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
     * Load the reference data into the database.
     */
    protected function loadReferenceData()
    {
        $bundles = $this->container->getParameter('kernel.bundles');
        if (isset($bundles['AcmeAppBundle'])) {
            $entityManager = $this->container->get('doctrine.orm.entity_manager');
            $referenceDataLoader = new ReferenceDataLoader();
            $referenceDataLoader->load($entityManager);
        }
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
     * @throws \Exception if no files can be loaded
     *
     * @return array
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
}
