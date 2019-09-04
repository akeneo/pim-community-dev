<?php

namespace Context;

use Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Behat\Testwork\Hook\Scope\BeforeSuiteScope;
use Context\Loader\ReferenceDataLoader;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Behat\Context\PimContext;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Dotenv\Dotenv;

/**
 * A context for initializing catalog configuration
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class CatalogConfigurationContext extends PimContext
{
    private const DB_TEMPLATE_BASE = 'pim_template';

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

        $is_catalog_loaded = false;
        if ($this->dbTemplateExistsForCatalog($catalog)) {
            try {
                $this->loadDbTemplateForCatalog($catalog);
                $is_catalog_loaded = true;
            } catch (\RuntimeException $e) {
                print_r(sprintf("Warning: Catalog can not be loaded from template: '%s'.", $e->getMessage()));
            }
        }

        if (!$is_catalog_loaded) {
            $this->loadCatalog($this->getConfigurationFiles($catalog));
            $this->saveDbTemplateForCatalog($catalog);
        }

        $this->getMainContext()->getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();
    }

    /**
     * @param BeforeSuiteScope $scope
     * @BeforeSuite
     */
    public static function cleanTemplates(BeforeSuiteScope $scope): void
    {
        $command = sprintf(
            "mysql %s --execute \"SHOW DATABASES LIKE '%s%%';\"",
            self::getMysqlCommandParameters(),
            self::DB_TEMPLATE_BASE
        );
        $res = shell_exec($command);
        $databases = array_filter(explode(PHP_EOL, $res));
        array_shift($databases);

        foreach ($databases as $database) {
            $command = sprintf(
                'mysql %s --execute="DROP DATABASE IF EXISTS %s;"',
                self::getMysqlCommandParameters(),
                $database
            );
            shell_exec($command);
        }
    }

    /**
     * @param string $catalog
     * @return bool
     */
    private function dbTemplateExistsForCatalog(string $catalog): bool
    {
        $db = $this->getMainContext()->getContainer()->get('doctrine.dbal.default_connection');
        $databaseInfos = $db
            ->query(sprintf("SHOW DATABASES LIKE '%s';", $this->getDbTemplateNameForCatalog($catalog)))
            ->fetchAll();

        return 1 === count($databaseInfos);
    }

    /**
     * @param string $catalog
     */
    private function loadDbTemplateForCatalog(string $catalog): void
    {
        $this->copyDatabase($this->getDbTemplateNameForCatalog($catalog), 'akeneo_pim');
    }

    /**
     * @param string $catalog
     */
    private function saveDbTemplateForCatalog(string $catalog): void
    {
        $this->copyDatabase('akeneo_pim', $this->getDbTemplateNameForCatalog($catalog));
    }

    /**
     * @param string $fromDatabase
     * @param string $toDatabase
     */
    private function copyDatabase(string $fromDatabase, string $toDatabase): void
    {
        $command = sprintf(
            'mysql %s --execute="DROP DATABASE IF EXISTS %s;"',
            self::getMysqlCommandParameters(),
            $toDatabase
        );
        self::execWithTimeout($command);

        $command = sprintf('mysql %s --execute="CREATE DATABASE %s;"', self::getMysqlCommandParameters(), $toDatabase);
        self::execWithTimeout($command);

        $command = sprintf(
            'mysql %s --execute="grant all privileges on %s.* to %s@\'%%\';"',
            self::getMysqlCommandParameters(),
            $toDatabase,
            'akeneo_pim'
        );
        self::execWithTimeout($command);

        $command = sprintf(
            'mysqldump %s %s | mysql %s %s',
            self::getMysqlCommandParameters(),
            $fromDatabase,
            self::getMysqlCommandParameters(),
            $toDatabase
        );
        self::execWithTimeout($command);
    }

    /**
     * Returns the exit code of the command.
     *
     * @param string $command
     * @param int    $timeout_in_second
     * @return int
     */
    private static function execWithTimeout(string $command, int $timeout_in_second = 10): int
    {
        $descriptorSpec = [['pipe', 'r'], ['pipe', 'w'], ['pipe', 'w']];
        $process = proc_open($command, $descriptorSpec, $pipes);

        $time_start = time();
        if (is_resource($process)) {
            while (is_resource($process)) {
                $status = proc_get_status($process);
                if (!$status['running']) {
                    return $status['exitcode'];
                }

                if ($timeout_in_second !== 0 && $timeout_in_second < time() - $time_start) {
                    proc_terminate($process, 9);

                    throw new \RuntimeException(sprintf("Timeout exceeded for command: '%s'.", $command));
                }

                usleep(100000); // sleep during 0.1 second
            }
        }

        return 1;
    }

    /**
     * @param string $catalog
     * @return string
     */
    private function getDbTemplateNameForCatalog(string $catalog): string
    {
        return sprintf('%s_%s', self::DB_TEMPLATE_BASE, $catalog);
    }

    /**
     * @return string
     */
    private static function getMysqlCommandParameters(): string
    {
        $envFiles = [
            __DIR__ . '/../../../../.env',
            __DIR__ . '/../../../../.env.local',
            __DIR__ . '/../../../../.env.behat',
        ];

        $env = new Dotenv();
        foreach ($envFiles as $envFile) {
            if (file_exists($envFile)) {
                $env->load($envFile);
            }
        }

        $port = $_ENV['APP_DATABASE_PORT'] ?? 3306;

        return sprintf(
            '--host %s --port %s --user %s --password="%s"',
            $_ENV['APP_DATABASE_HOST'],
            'null' === $port ? 3306 : $port,
            $_ENV['APP_DATABASE_ROOT_USER'] ?? 'root',
            $_ENV['APP_DATABASE_ROOT_PASSWORD'] ?? 'root'
        );
    }

    /**
     * @param string $entity
     *
     * @Given /^there is no "([^"]*)" in the catalog$/
     */
    public function thereIsNoSuchEntityInTheCatalog($entity)
    {
        $db = $this->getMainContext()->getContainer()->get('doctrine.dbal.default_connection');
        $tablesToPurge = [];

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
