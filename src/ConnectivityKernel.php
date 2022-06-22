<?php

declare(strict_types=1);

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * PIM Kernel
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConnectivityKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function registerBundles(): iterable
    {
        $bundles = [
//            Akeneo\Catalogs\Infrastructure\Symfony\AkeneoCatalogsBundle::class => ['all' => true],
//            Akeneo\Channel\Infrastructure\Symfony\AkeneoChannelBundle::class => ['all' => true],
            Akeneo\Connectivity\Connection\Infrastructure\Symfony\AkeneoConnectivityConnectionBundle::class => ['all' => true],
//            Akeneo\FreeTrial\Infrastructure\Symfony\AkeneoCommunityFreeTrialBundle::class => ['all' => true],
//            Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Symfony\AkeneoDataQualityInsightsBundle::class => ['all' => true],
//            Akeneo\Pim\Enrichment\Bundle\AkeneoPimEnrichmentBundle::class => ['all' => true],
//            Akeneo\Pim\Enrichment\Category\Infrastructure\Symfony\AkeneoPimEnrichmentCategoryBundle::class => ['all' => true],
//            Akeneo\Pim\Enrichment\Product\Infrastructure\Symfony\AkeneoPimEnrichmentProductBundle::class => ['all' => true],
//            Akeneo\Pim\Structure\Bundle\AkeneoPimStructureBundle::class => ['all' => true],
//            Akeneo\Platform\Bundle\AnalyticsBundle\PimAnalyticsBundle::class => ['all' => true],
//            Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\PimCatalogVolumeMonitoringBundle::class => ['all' => true],
//            Akeneo\Platform\Bundle\DashboardBundle\PimDashboardBundle::class => ['all' => true],
            Akeneo\Platform\Bundle\FeatureFlagBundle\AkeneoFeatureFlagBundle::class => ['all' => true],
            Akeneo\Platform\Bundle\FrameworkBundle\PimFrameworkBundle::class => ['all' => true],
//            Akeneo\Platform\Bundle\ImportExportBundle\PimImportExportBundle::class => ['all' => true],
//            Akeneo\Platform\Bundle\InstallerBundle\PimInstallerBundle::class => ['all' => true],
            Akeneo\Platform\Bundle\NotificationBundle\PimNotificationBundle::class => ['all' => true],
            Akeneo\Platform\Bundle\PimVersionBundle\PimVersionBundle::class => ['all' => true],
//            Akeneo\Platform\Bundle\UIBundle\PimUIBundle::class => ['all' => true],
//            Akeneo\Platform\CommunicationChannel\Infrastructure\Framework\Symfony\AkeneoCommunicationChannelBundle::class => ['all' => true],
//            Akeneo\Platform\Job\Infrastructure\Symfony\AkeneoJobBundle::class => ['all' => true],
            Akeneo\Tool\Bundle\ApiBundle\PimApiBundle::class => ['all' => true],
//            Akeneo\Tool\Bundle\BatchBundle\AkeneoBatchBundle::class => ['all' => true],
//            Akeneo\Tool\Bundle\BatchQueueBundle\AkeneoBatchQueueBundle::class => ['all' => true],
//            Akeneo\Tool\Bundle\ClassificationBundle\AkeneoClassificationBundle::class => ['all' => true],
//            Akeneo\Tool\Bundle\ConnectorBundle\PimConnectorBundle::class => ['all' => true],
            Akeneo\Tool\Bundle\ElasticsearchBundle\AkeneoElasticsearchBundle::class => ['all' => true],
            Akeneo\Tool\Bundle\FileStorageBundle\AkeneoFileStorageBundle::class => ['all' => true],
//            Akeneo\Tool\Bundle\MeasureBundle\AkeneoMeasureBundle::class => ['all' => true],
            Akeneo\Tool\Bundle\MessengerBundle\AkeneoMessengerBundle::class => ['all' => true],
            Akeneo\Tool\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle::class => ['all' => true],
//            Akeneo\Tool\Bundle\VersioningBundle\AkeneoVersioningBundle::class => ['all' => true],
            Akeneo\UserManagement\Bundle\PimUserBundle::class => ['all' => true],
            Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
            Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['all' => true],
            Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
            FOS\JsRoutingBundle\FOSJsRoutingBundle::class => ['all' => true],
            FOS\OAuthServerBundle\FOSOAuthServerBundle::class => ['all' => true],
            FOS\RestBundle\FOSRestBundle::class => ['all' => true],
            Liip\ImagineBundle\LiipImagineBundle::class => ['all' => true],
            Oneup\FlysystemBundle\OneupFlysystemBundle::class => ['all' => true],
//            Oro\Bundle\ConfigBundle\OroConfigBundle::class => ['all' => true],
//            Oro\Bundle\DataGridBundle\OroDataGridBundle::class => ['all' => true],
//            Oro\Bundle\FilterBundle\OroFilterBundle::class => ['all' => true],
//            Oro\Bundle\PimDataGridBundle\PimDataGridBundle::class => ['all' => true],
//            Oro\Bundle\PimFilterBundle\PimFilterBundle::class => ['all' => true],
            Oro\Bundle\SecurityBundle\OroSecurityBundle::class => ['all' => true],
//            Oro\Bundle\TranslationBundle\OroTranslationBundle::class => ['all' => true],
            Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle::class => ['all' => true],
            Symfony\Bundle\AclBundle\AclBundle::class => ['all' => true],
            Symfony\Bundle\DebugBundle\DebugBundle::class => ['dev' => true, 'test' => true, 'behat' => true],
            Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
            Symfony\Bundle\MonologBundle\MonologBundle::class => ['all' => true],
            Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
            Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle::class => ['all' => true],
            Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
            Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true, 'behat' => true],
            // Tests related bundles
            Acme\Bundle\AppBundle\AcmeAppBundle::class => ['dev' => true, 'test' => true, 'behat' => true],
            Akeneo\Test\IntegrationTestsBundle\AkeneoIntegrationTestsBundle::class => ['dev' => true, 'test' => true],
            FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle::class => ['behat' => true, 'test' => true, 'test_fake' => true],
        ];

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
        $container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/config';

        $loader->load($confDir.'/packages/akeneo_api.yml');
        $loader->load($confDir.'/packages/akeneo_elasticsearch.yml');
        $loader->load($confDir.'/packages/akeneo_feature_flag.yml');
        $loader->load($confDir.'/packages/akeneo_pim_user.yml');
        $loader->load($confDir.'/packages/akeneo_storage_utils.yml');
        $loader->load($confDir.'/packages/doctrine.yml');
        $loader->load($confDir.'/packages/fos_auth_server.yml');
        $loader->load($confDir.'/packages/fos_js_routing.yml');
        $loader->load($confDir.'/packages/fos_rest.yml');
        $loader->load($confDir.'/packages/framework.yml');
        $loader->load($confDir.'/packages/liip_imagine.yml');
        $loader->load($confDir.'/packages/messenger.yml');
        $loader->load($confDir.'/packages/monolog.yml');
        $loader->load($confDir.'/packages/oneup_flysystem.yml');
        $loader->load($confDir.'/packages/security.yml');
        $loader->load($confDir.'/packages/sensio_framework_extra.yml');
        $loader->load($confDir.'/packages/swiftmailer.yml');
        $loader->load($confDir.'/packages/twig.yml');
        $loader->load($confDir.'/packages/test/framework.yml');
        $loader->load($confDir.'/packages/test/messenger.yml');
        $loader->load($confDir.'/packages/test/monolog.yml');
        $loader->load($confDir.'/packages/test/oneup_flysystem.yml');
        $loader->load($confDir.'/packages/test/security.yml');

        $loader->load($confDir.'/{services}/*.yml', 'glob');
        $loader->load($confDir.'/{services}/'.$this->environment.'/**/*.yml', 'glob');

        $loader->load($confDir.'/fake.yml');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes): void
    {
        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*.yml', '/', 'glob');
        $routes->import($confDir.'/{routes}/*.yml', '/', 'glob');
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
