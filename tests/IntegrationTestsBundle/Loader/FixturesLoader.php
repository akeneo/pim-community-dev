<?php

declare(strict_types=1);

namespace Akeneo\Test\IntegrationTestsBundle\Loader;

use Akeneo\Test\Integration\Configuration;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FixturesLoader implements FixturesLoaderInterface
{
    const CACHE_DIR = '/pim-integration-tests-data-cache/';

    /** @var KernelInterface */
    protected $kernel;

    /** @var DatabaseSchemaHandler */
    protected $databaseSchemaHandler;

    /** @var SystemUserAuthenticator */
    protected $systemUserAuthenticator;

    /** @var ContainerInterface */
    protected $container;

    /** @var Application */
    protected $cli;

    /** @var Configuration */
    protected $configuration;

    /**
     * @param KernelInterface         $kernel
     * @param DatabaseSchemaHandler   $databaseSchemaHandler
     * @param SystemUserAuthenticator $systemUserAuthenticator
     * @param Configuration           $configuration
     */
    public function __construct(
        KernelInterface $kernel,
        DatabaseSchemaHandler $databaseSchemaHandler,
        SystemUserAuthenticator $systemUserAuthenticator,
        Configuration $configuration
    ) {
        $this->kernel = $kernel;
        $this->databaseSchemaHandler = $databaseSchemaHandler;
        $this->systemUserAuthenticator = $systemUserAuthenticator;
        $this->configuration = $configuration;

        $this->container = $kernel->getContainer();
        $this->cli = new Application($kernel);
        $this->cli->setAutoExit(false);
    }

    public function __destruct()
    {
        // close the connection created specifically for this repository
        // TODO: to remove when TIP-385 will be done
        $doctrineJobRepository = $this->container->get('akeneo_batch.job_repository');
        $doctrineJobRepository->getJobManager()->getConnection()->close();
    }

    /**
     * {@inheritdoc}
     *
     * The elastic search indexes are reset here, at the same time than the database.
     * However, the second index is not reset directly after the first one, as it could
     * prevent the first one to be correctly dilated.
     */
    public function load(): void
    {
        $this->systemUserAuthenticator->createSystemUser();

        $this->resetElasticsearchIndex();

        $files = $this->getFilesToLoad($this->configuration->getCatalogDirectories());
        $fixturesHash = $this->getHashForFiles($files);

        $dumpFile = sys_get_temp_dir().self::CACHE_DIR.$fixturesHash.'.sql';

        if (file_exists($dumpFile)) {
            $this->dropDatabase();
            $this->createDatabase();
            $this->restoreDatabase($dumpFile);
            $this->clearAclCache();

            $this->indexProductModels();
            $this->indexProducts();

            return;
        }

        $this->databaseSchemaHandler->reset();

        $this->loadData();
        $this->dumpDatabase($dumpFile);
    }

    protected function loadData(): void
    {
        $files = $this->getFilesToLoad($this->configuration->getCatalogDirectories());
        $filesByType = $this->getFilesToLoadByType($files);

        $this->loadSqlFiles($filesByType['sql']);
        $this->loadImportFiles($filesByType['import']);
        $this->loadReferenceData();
    }

    /**
     * Returns a unique hash for the specified set of files. If one the file changes, the hash changes.
     *
     * @param array $files
     *
     * @return string
     */
    protected function getHashForFiles(array $files): string
    {
        $hashes = array_map('sha1_file', $files);

        return sha1(implode(':', $hashes));
    }

    /**
     * Load SQL file directly by a regular SQL query
     *
     * @param array $files
     */
    protected function loadSqlFiles(array $files): void
    {
        foreach ($files as $file) {
            $this->execCommand([
                'mysql',
                '-h '.$this->container->getParameter('database_host'),
                '-u '.$this->container->getParameter('database_user'),
                '-p'.$this->container->getParameter('database_password'),
                $this->container->getParameter('database_name'),
                sprintf('< %s', $file),
            ]);
        }
    }

    /**
     * Load import files via akeneo:batch:job
     *
     * @param array $files
     *
     * @throws \RuntimeException
     */
    protected function loadImportFiles(array $files): void
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

        // install the catalog via the job instances
        $jobInstances = $jobLoader->getLoadedJobInstances();
        foreach ($jobInstances as $jobInstance) {
            $input = new ArrayInput([
                'command'  => 'akeneo:batch:job',
                'code'     => $jobInstance->getCode(),
                '--no-log' => true,
                '-v'       => true
            ]);
            $output = new BufferedOutput();
            $exitCode = $this->cli->run($input, $output);

            if (0 !== $exitCode) {
                throw new \RuntimeException(sprintf('Catalog not installable! "%s"', $output->fetch()));
            }
        }

        $jobLoader->deleteJobInstances();
    }

    /**
     * Load the reference data into the database.
     */
    protected function loadReferenceData(): void
    {
        $referenceDataLoader = $this->container->get('akeneo_integration_tests.loader.reference_data_loader');
        $referenceDataLoader->load();
    }

    /**
     * Separate files to load by their type. They can be:
     *  - regular files to load from an import.
     *  - SQL files
     *
     * @param array $files
     *
     * @return array
     */
    protected function getFilesToLoadByType(array $files): array
    {
        $filesByType = [
            'sql' => [],
            'import' => [],
        ];

        foreach ($files as $filePath) {
            $realPathParts = pathinfo($filePath);

            // do not try to load files without extension
            if (!isset($realPathParts['extension'])) {
                continue;
            }

            switch ($realPathParts['extension']) {
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
    protected function getFilesToLoad(array $directories): array
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
            $files[] = $realFilePath;
        }

        return $files;
    }

    protected function dropDatabase(): void
    {
        $this->execCommand([
            'mysql',
            '-h '.$this->container->getParameter('database_host'),
            '-u '.$this->container->getParameter('database_user'),
            '-p'.$this->container->getParameter('database_password'),
            sprintf('-e "DROP DATABASE IF EXISTS %s;"', $this->container->getParameter('database_name')),
        ]);
    }

    protected function createDatabase(): void
    {
        $this->execCommand([
            'mysql',
            '-h '.$this->container->getParameter('database_host'),
            '-u '.$this->container->getParameter('database_user'),
            '-p'.$this->container->getParameter('database_password'),
            sprintf('-e "CREATE DATABASE %s;"', $this->container->getParameter('database_name')),
        ]);
    }

    /**
     * @param string $filepath
     */
    protected function dumpDatabase($filepath): void
    {
        $dir = dirname($filepath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->execCommand([
            'mysqldump',
            '-h '.$this->container->getParameter('database_host'),
            '-u '.$this->container->getParameter('database_user'),
            '-p'.$this->container->getParameter('database_password'),
            '--skip-add-drop-table',
            '--quick',
            $this->container->getParameter('database_name'),
            '> '.$filepath,
        ]);
    }

    /**
     * @param string $filepath
     */
    protected function restoreDatabase($filepath): void
    {
        $this->execCommand([
            'mysql',
            '-h '.$this->container->getParameter('database_host'),
            '-u '.$this->container->getParameter('database_user'),
            '-p'.$this->container->getParameter('database_password'),
            $this->container->getParameter('database_name'),
            '< '.$filepath,
        ]);
    }

    /**
     * @param string[] $arguments
     * @param int      $timeout
     *
     * @return string
     */
    protected function execCommand(array $arguments, $timeout = 120): string
    {
        $process = new Process(implode(' ', $arguments));
        $process->setTimeout($timeout);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    /**
     * Clear Oro cache about Acl.
     * This cache should be cleared when loading the fixtures with mysql dump.
     * It avoids inconsistency between the cache and the new data in the database.
     */
    protected function clearAclCache(): void
    {
        $aclManager = $this->container->get('oro_security.acl.manager');
        $aclManager->clearCache();
    }

    protected function indexProducts(): void
    {
        $products = $this->container->get('pim_catalog.repository.product')->findAll();
        $this->container->get('pim_catalog.elasticsearch.indexer.product')->indexAll($products);
    }

    protected function indexProductModels(): void
    {
        $productModels = $this->container->get('pim_catalog.repository.product_model')->findAll();
        $this->container->get('pim_catalog.elasticsearch.indexer.product_model')->indexAll($productModels);
    }

    protected function resetElasticsearchIndex(): void
    {
        $clientRegistry = $this->container->get('akeneo_elasticsearch.registry.clients');
        $clients = $clientRegistry->getClients();

        foreach ($clients as $client) {
            $client->resetIndex();
        }
    }
}
