<?php

namespace Akeneo\Test\Integration;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

/**
 * @author    Yohan Blain <yohan.blain@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FixturesLoader
{
    const CACHE_DIR = '/pim-integration-tests-data-cache/';

    /** @var KernelInterface */
    protected $kernel;

    /** @var Configuration */
    protected $configuration;

    /** @var DatabaseSchemaHandler */
    protected $databaseSchemaHandler;

    /** @var ContainerInterface */
    protected $container;

    /** @var Application */
    protected $cli;

    /**
     * @param KernelInterface       $kernel
     * @param Configuration         $configuration
     * @param DatabaseSchemaHandler $databaseSchemaHandler
     */
    public function __construct(
        KernelInterface $kernel,
        Configuration $configuration,
        DatabaseSchemaHandler $databaseSchemaHandler
    ) {
        $this->kernel = $kernel;
        $this->configuration = $configuration;
        $this->databaseSchemaHandler = $databaseSchemaHandler;

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
     * Loads test catalog.
     *
     * The elastic search indexes are reset here, at the same time than the database.
     * However, the second index is not reset directly after the first one, as it could
     * prevent the first one to be correctly dilated.
     *
     * @throws \Exception
     */
    public function load()
    {
        $this->container->get('akeneo_elasticsearch.client.product')->resetIndex();
        $this->container->get('akeneo_elasticsearch.client.product_model')->resetIndex();
        $this->container->get('akeneo_elasticsearch.client.product_and_product_model')->resetIndex();

        $files = $this->getFilesToLoad($this->configuration->getCatalogDirectories());
        $fixturesHash = $this->getHashForFiles($files);

        $dumpFile = sys_get_temp_dir().self::CACHE_DIR.$fixturesHash.'.sql';

        if (file_exists($dumpFile)) {
            $this->dropDatabase();
            $this->createDatabase();
            $this->restoreDatabase($dumpFile);
            $this->clearAclCache();
            $this->createUserSystem();

            $this->indexProductModels();
            $this->indexProducts();

            return;
        }

        $this->databaseSchemaHandler->reset();

        $this->loadData();
        $this->dumpDatabase($dumpFile);
    }

    protected function loadData()
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
    protected function getHashForFiles(array $files)
    {
        $hashes = array_map('sha1_file', $files);

        return sha1(implode(':', $hashes));
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
     * Load import files via akeneo:batch:job
     *
     * @param array $files
     *
     * @throws \RuntimeException
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
     *
     * @param array $files
     *
     * @return array
     */
    protected function getFilesToLoadByType(array $files)
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
            $files[] = $realFilePath;
        }

        return $files;
    }

    protected function dropDatabase()
    {
        $this->execCommand([
            'mysql',
            '-h '.$this->container->getParameter('database_host'),
            '-u '.$this->container->getParameter('database_user'),
            '-p'.$this->container->getParameter('database_password'),
            sprintf('-e "DROP DATABASE IF EXISTS %s;"', $this->container->getParameter('database_name')),
        ]);
    }

    protected function createDatabase()
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
    protected function dumpDatabase($filepath)
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
    protected function restoreDatabase($filepath)
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
    protected function execCommand(array $arguments, $timeout = 120)
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
     * Create a token with a user system with all access
     */
    private function createUserSystem()
    {
        $user = $this->container->get('pim_user.factory.user')->create();
        $user->setUsername('system');
        $groups = $this->container->get('pim_user.repository.group')->findAll();

        foreach ($groups as $group) {
            $user->addGroup($group);
        }

        $roles = $this->container->get('pim_user.repository.role')->findAll();
        foreach ($roles as $role) {
            $user->addRole($role);
        }

        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->container->get('security.token_storage')->setToken($token);
    }

    /**
     * Clear Oro cache about Acl.
     * This cache should be cleared when loading the fixtures with mysql dump.
     * It avoids inconsistency between the cache and the new data in the database.
     */
    protected function clearAclCache()
    {
        $aclCache = $this->container->get('security.acl.cache');
        $aclCache->clearCache();
    }

    protected function indexProducts()
    {
        $products = $this->container->get('pim_catalog.repository.product')->findAll();
        $this->container->get('pim_catalog.elasticsearch.indexer.product')->indexAll($products);
    }

    protected function indexProductModels()
    {
        $productModels = $this->container->get('pim_catalog.repository.product_model')->findAll();
        $this->container->get('pim_catalog.elasticsearch.indexer.product_model')->indexAll($productModels);
    }
}
