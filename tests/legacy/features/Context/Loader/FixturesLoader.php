<?php

declare(strict_types=1);

namespace Context\Loader;

use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductIndexer;
use Akeneo\Pim\Enrichment\Bundle\Elasticsearch\Indexer\ProductModelIndexer;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductModelRepositoryInterface;
use Akeneo\Pim\Enrichment\Component\Product\Repository\ProductRepositoryInterface;
use Akeneo\Platform\Bundle\InstallerBundle\FixtureLoader\FixtureJobLoader;
use Akeneo\Test\IntegrationTestsBundle\Loader\DatabaseSchemaHandler;
use Akeneo\Test\IntegrationTestsBundle\Security\SystemUserAuthenticator;
use Akeneo\Tool\Bundle\ElasticsearchBundle\Client;
use Akeneo\Tool\Bundle\ElasticsearchBundle\ClientRegistry;
use Akeneo\Tool\Component\StorageUtils\Cache\EntityManagerClearerInterface;
use Doctrine\Common\DataFixtures\Event\Listener\ORMReferenceListener;
use Doctrine\Common\DataFixtures\ReferenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\SecurityBundle\Acl\Persistence\AclManager;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2019 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class FixturesLoader
{
    /** @var DatabaseSchemaHandler */
    private $databaseSchemaHandler;

    /** @var SystemUserAuthenticator */
    private $systemUserAuthenticator;

    /** @var ReferenceRepository Fixture reference repository */
    private $referenceRepository;

    /** @var ContainerInterface */
    private $container;

    const CACHE_DIR = '/pim-legacy-tests-data-cache/';

    /**
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->databaseSchemaHandler = new DatabaseSchemaHandler($container->get('kernel'));
        $this->systemUserAuthenticator = new SystemUserAuthenticator($this->container);
    }

    /**
     * @param string[] $configurationFiles
     */
    public function load(array $configurationFiles)
    {
        $this->initializeReferenceRepository();
        $this->resetElasticsearchIndex();
        $this->databaseSchemaHandler->reset();
        $this->getAclManager()->clearCache();

        $this->loadCatalogOrRestoreDatabase($configurationFiles);

        $this->systemUserAuthenticator->createSystemUser();
        $this->getCacheClearer()->clear();
    }

    /**
     * @param string[] $configurationFiles
     */
    private function loadCatalogOrRestoreDatabase(array $configurationFiles)
    {
        $fixturesHash = $this->getHashForFiles($configurationFiles);
        $dumpFile = sys_get_temp_dir() . self::CACHE_DIR . $fixturesHash . '.sql';

        if (file_exists($dumpFile)) {
            $this->restoreDatabase($dumpFile);
            $this->getProductModelIndexer()->indexAll($this->getProductModelRepository()->findAll());
            $this->getProductIndexer()->indexAll($this->getProductRepository()->findAll());

            return;
        }

        $this->loadCatalog($configurationFiles);
        $this->dumpDatabase($dumpFile);
    }

    /**
     * Initialize the reference repository
     */
    private function initializeReferenceRepository()
    {
        $this->referenceRepository = new ReferenceRepository($this->getEntityManager());
        $listener = new ORMReferenceListener($this->referenceRepository);
        $this->getEntityManager()->getEventManager()->addEventSubscriber($listener);
    }

    private function resetElasticsearchIndex(): void
    {
        foreach ($this->getElasticsearchClients()->getClients() as $client) {
            $client->resetIndex();
        }
    }

    /**
     * Returns a unique hash for the specified set of files. If one the file changes, the hash changes.
     *
     * @param array $files
     *
     * @return string
     */
    private function getHashForFiles(array $files): string
    {
        $hashes = array_map('sha1_file', $files);

        return sha1(implode(':', $hashes));
    }

    /**
     * @param string $filepath
     */
    private function restoreDatabase($filepath): void
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
    private function execCommand(array $arguments, $timeout = 120): string
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
     * @param string[] $files Catalog configuration files to load
     *
     * @throws \Exception
     */
    private function loadCatalog($files)
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
        $application = new Application($this->container->get('kernel'));
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
        $bundles = $this->container->getParameter('kernel.bundles');
        if (isset($bundles['AcmeAppBundle'])) {
            $referenceDataLoader = new ReferenceDataLoader();
            $referenceDataLoader->load($this->getEntityManager());
        }

        $this->getElasticsearchProductClient()->refreshIndex();
    }

    /**
     * @param string $filepath
     */
    private function dumpDatabase($filepath): void
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
            '--no-create-info',
            '--quick',
            $this->container->getParameter('database_name'),
            '> '.$filepath,
        ]);
    }

    /**
     * @return EntityManagerInterface
     */
    private function getEntityManager(): EntityManagerInterface
    {
        return $this->container->get('doctrine')->getManager();
    }

    /**
     * @return FixtureJobLoader
     */
    private function getFixtureJobLoader(): FixtureJobLoader
    {
        return $this->container->get('pim_installer.fixture_loader.job_loader');
    }

    /**
     * @return Client
     */
    private function getElasticsearchProductClient(): Client
    {
        return $this->container->get('akeneo_elasticsearch.client.product');
    }

    /**
     * @return ProductRepositoryInterface
     */
    private function getProductRepository(): ProductRepositoryInterface
    {
        return $this->container->get('pim_catalog.repository.product');
    }

    /**
     * @return ProductModelRepositoryInterface
     */
    private function getProductModelRepository(): ProductModelRepositoryInterface
    {
        return $this->container->get('pim_catalog.repository.product_model');
    }

    /**
     * @return ProductModelIndexer
     */
    private function getProductModelIndexer(): ProductModelIndexer
    {
        return $this->container->get('pim_catalog.elasticsearch.indexer.product_model');
    }

    /**
     * @return AclManager
     */
    private function getAclManager(): AclManager
    {
        return $this->container->get('oro_security.acl.manager');
    }

    /**
     * @return ClientRegistry
     */
    private function getElasticsearchClients(): ClientRegistry
    {
        return $this->container->get('akeneo_elasticsearch.registry.clients');
    }

    /**
     * @return ProductIndexer
     */
    private function getProductIndexer(): ProductIndexer
    {
        return $this->container->get('pim_catalog.elasticsearch.indexer.product');
    }

    /**
     * @return EntityManagerClearerInterface
     */
    private function getCacheClearer(): EntityManagerClearerInterface
    {
        return $this->container->get('pim_connector.doctrine.cache_clearer');
    }
}
