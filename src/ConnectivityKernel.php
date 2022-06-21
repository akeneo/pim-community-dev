<?php

declare(strict_types=1);

use Akeneo\Channel\Infrastructure\Component\Model\ChannelInterface;
use Akeneo\Channel\Infrastructure\Component\Repository\LocaleRepositoryInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\MockObject\MockBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Routing\RouteCollectionBuilder;

class ConnectivityKernel extends Kernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $bundles = require $this->getProjectDir() . '/config/bundles-connectivity.php';
        $bundles += require $this->getProjectDir() . '/config/bundles.test.php';


        foreach ($bundles as $class => $envs) {
            if ($envs[$this->environment] ?? $envs['all'] ?? false) {
                yield new $class();
            }
        }
    }

    public function getProjectDir(): string
    {
        return \dirname(__DIR__);
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader): void
    {
        $container->addResource(new FileResource($this->getProjectDir().'/config/bundles-connectivity.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/config';

$loader->load($confDir.'/{packages}/akeneo_api.yml', 'glob');
//$loader->load($confDir.'/{packages}/akeneo_batch.yml', 'glob');
$loader->load($confDir.'/{packages}/akeneo_elasticsearch.yml', 'glob');
$loader->load($confDir.'/{packages}/akeneo_feature_flag.yml', 'glob');
//$loader->load($confDir.'/{packages}/akeneo_pim_enrichment.yml', 'glob');
$loader->load($confDir.'/{packages}/akeneo_pim_user.yml', 'glob');
$loader->load($confDir.'/{packages}/akeneo_storage_utils.yml', 'glob');
$loader->load($confDir.'/{packages}/doctrine.yml', 'glob');
$loader->load($confDir.'/{packages}/fos_auth_server.yml', 'glob');
$loader->load($confDir.'/{packages}/fos_js_routing.yml', 'glob');
$loader->load($confDir.'/{packages}/fos_rest.yml', 'glob');
$loader->load($confDir.'/{packages}/framework.yml', 'glob');
$loader->load($confDir.'/{packages}/liip_imagine.yml', 'glob');
$loader->load($confDir.'/{packages}/messenger.yml', 'glob');
$loader->load($confDir.'/{packages}/monolog.yml', 'glob');
$loader->load($confDir.'/{packages}/oneup_flysystem.yml', 'glob');
//$loader->load($confDir.'/{packages}/oro_filter.yml', 'glob');
$loader->load($confDir.'/{packages}/oro_translation.yml', 'glob');
$loader->load($confDir.'/{packages}/security.yml', 'glob');
$loader->load($confDir.'/{packages}/sensio_framework_extra.yml', 'glob');
$loader->load($confDir.'/{packages}/swiftmailer.yml', 'glob');
$loader->load($confDir.'/{packages}/twig.yml', 'glob');
$loader->load($confDir.'/{packages}/test/framework.yml' , 'glob');
$loader->load($confDir.'/{packages}/test/messenger.yml' , 'glob');
$loader->load($confDir.'/{packages}/test/monolog.yml' , 'glob');
$loader->load($confDir.'/{packages}/test/oneup_flysystem.yml' , 'glob');
$loader->load($confDir.'/{packages}/test/security.yml' , 'glob');

        $loader->load($confDir.'/{services}/*.yml', 'glob');
        $loader->load($confDir.'/{services}/'.$this->environment.'/**/*.yml', 'glob');
    }

    protected function initializeContainer()
    {
        parent::initializeContainer();


        $this->getContainer()->set('pim_catalog.repository.locale', new class() implements LocaleRepositoryInterface  {
            public function getActivatedLocales() {
                return [];
            }
            public function getActivatedLocaleCodes() {}
            public function getActivatedLocalesQB() {}
            public function getDeletedLocalesForChannel(ChannelInterface $channel){}
            public function countAllActivated() {}

            public function getIdentifierProperties()
            {
                // TODO: Implement getIdentifierProperties() method.
            }

            public function findOneByIdentifier($identifier)
            {
                // TODO: Implement findOneByIdentifier() method.
            }

            public function find($id)
            {
                // TODO: Implement find() method.
            }

            public function findAll()
            {
                // TODO: Implement findAll() method.
            }

            public function findBy(array $criteria, ?array $orderBy = null, $limit = null, $offset = null)
            {
                // TODO: Implement findBy() method.
            }

            public function findOneBy(array $criteria)
            {
                // TODO: Implement findOneBy() method.
            }

            public function getClassName()
            {
                // TODO: Implement getClassName() method.
            }
        });
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*.yml', '/', 'glob');
//        $routes->import($confDir.'/{routes}/*.yml', '/', 'glob');
        $routes->import($confDir.'/{routes}/routes-connectivity.yml', '/', 'glob');
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir(): string
    {
        return $this->getProjectDir().'/var/cache/'.$this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir(): string
    {
        return $this->getProjectDir().'/var/logs';
    }
}
