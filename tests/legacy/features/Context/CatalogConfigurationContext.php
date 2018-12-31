<?php

namespace Context;

use Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader;
use Akeneo\Test\IntegrationTestsBundle\Loader\DatabaseSchemaHandler;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Context\Loader\ReferenceDataLoader;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Pim\Behat\Context\PimContext;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

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

    private $databaseSchemaHandler;
    private $systemUserAuthenticator;
    const CACHE_DIR = '/pim-legacy-tests-data-cache/';

    /**
     * @param string $catalog
     *
     * @Given /^(?:a|an|the) "([^"]*)" catalog configuration$/
     */
    public function aCatalogConfiguration($catalog)
    {
        $time = microtime(true);

        $this->databaseSchemaHandler = new DatabaseSchemaHandler($this->getKernel());
        $this->systemUserAuthenticator = new SystemUserAuthenticator($this->getKernel()->getContainer());

        $this->initializeReferenceRepository();

        $this->resetElasticsearchIndex();
        $files = $this->getConfigurationFiles($catalog);
        $fixturesHash = $this->getHashForFiles($files);

        $dumpFile = sys_get_temp_dir().self::CACHE_DIR.$fixturesHash.'.sql';

        if (file_exists($dumpFile)) {
            $this->databaseSchemaHandler->reset();

            $this->restoreDatabase($dumpFile);
            $this->clearAclCache();

            $this->systemUserAuthenticator->createSystemUser();

            $this->indexProductModels();
            $this->indexProducts();

            $this->getMainContext()->getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();
//            var_dump("File already existing !" . $dumpFile);
//            var_dump(microtime(true) - $time);

//            return;
        } else {

            $this->databaseSchemaHandler->reset();
            $this->clearAclCache();

            $this->loadCatalog($files);

            $this->dumpDatabase($dumpFile);

            $this->systemUserAuthenticator->createSystemUser();

            $this->getMainContext()->getContainer()->get('pim_connector.doctrine.cache_clearer')->clear();

//            var_dump("File stored !" . $dumpFile);
//            var_dump(microtime(true) - $time);
        }
    }

    protected function resetElasticsearchIndex(): void
    {
        $clientRegistry = $this->getKernel()->getContainer()->get('akeneo_elasticsearch.registry.clients');
        $clients = $clientRegistry->getClients();

        foreach ($clients as $client) {
            $client->resetIndex();
        }
    }

    protected function getHashForFiles(array $files): string
    {
        $hashes = array_map('sha1_file', $files);

        return sha1(implode(':', $hashes));
    }

    protected function restoreDatabase($filepath): void
    {
        $this->execCommand([
            'mysql',
            '-h '.$this->getKernel()->getContainer()->getParameter('database_host'),
            '-u '.$this->getKernel()->getContainer()->getParameter('database_user'),
            '-p'.$this->getKernel()->getContainer()->getParameter('database_password'),
            $this->getKernel()->getContainer()->getParameter('database_name'),
            '< '.$filepath,
        ]);
    }

    protected function clearAclCache(): void
    {
        $aclManager = $this->getKernel()->getContainer()->get('oro_security.acl.manager');
        $aclManager->clearCache();
    }

    protected function indexProducts(): void
    {
        $products = $this->getKernel()->getContainer()->get('pim_catalog.repository.product')->findAll();
        $this->getKernel()->getContainer()->get('pim_catalog.elasticsearch.indexer.product')->indexAll($products);
    }

    protected function indexProductModels(): void
    {
        $productModels = $this->getKernel()->getContainer()->get('pim_catalog.repository.product_model')->findAll();
        $this->getKernel()->getContainer()->get('pim_catalog.elasticsearch.indexer.product_model')->indexAll($productModels);
    }

    protected function dumpDatabase($filepath): void
    {
        $dir = dirname($filepath);
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        $this->execCommand([
            'mysqldump',
            '-h '.$this->getKernel()->getContainer()->getParameter('database_host'),
            '-u '.$this->getKernel()->getContainer()->getParameter('database_user'),
            '-p'.$this->getKernel()->getContainer()->getParameter('database_password'),
            '--no-create-info',
            '--quick',
            $this->getKernel()->getContainer()->getParameter('database_name'),
            '> '.$filepath,
        ]);
    }

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
                $this->getContainer()->get('akeneo_elasticsearch.client.product')->resetIndex();
                $this->getContainer()->get('akeneo_elasticsearch.client.product')->refreshIndex();
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
        return $this->getContainer()->get('akeneo_elasticsearch.client.product');
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
