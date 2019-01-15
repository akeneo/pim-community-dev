<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * PIM AppKernel
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppKernel extends Kernel
{
    /**
     * Registers your custom bundles
     *
     * @return array
     */
    protected function registerProjectBundles()
    {
        return [
            // your app bundles should be registered here
            new Acme\Bundle\AppBundle\AcmeAppBundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = $this->registerProjectBundles();

        if (in_array($this->getEnvironment(), array('dev', 'test', 'behat'))) {
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
        }

        $bundles = array_merge(
            $this->getSymfonyBundles(),
            $this->getOroDependencies(),
            $this->getOroBundles(),
            $this->getPimDependenciesBundles(),
            $this->getPimBundles(),
            $bundles
        );

        return $bundles;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/config_' . $this->getEnvironment() . '.yml');

        if (is_file($file = $this->getRootDir() . '/config/config_' . $this->getEnvironment() . '_local.yml')) {
            $loader->load($file);
        }
    }

    /**
     * Bundles coming from the PIM
     *
     * @return array
     */
    protected function getPimBundles()
    {
        return [
            // BAP overriden bundles
            new Oro\Bundle\PimFilterBundle\PimFilterBundle(),
            new Oro\Bundle\PimDataGridBundle\PimDataGridBundle(),
            new Akeneo\UserManagement\Bundle\PimUserBundle(),

            // Channel bundles
            new Akeneo\Channel\Bundle\AkeneoChannelBundle(),

            // PIM bundles
            new Akeneo\Pim\Enrichment\Bundle\AkeneoPimEnrichmentBundle(),
            new Akeneo\Pim\Structure\Bundle\AkeneoPimStructureBundle(),

            // Platform bundles
            new Akeneo\Platform\Bundle\DashboardBundle\PimDashboardBundle(),
            new Akeneo\Platform\Bundle\AnalyticsBundle\PimAnalyticsBundle(),
            new Akeneo\Platform\Bundle\ImportExportBundle\PimImportExportBundle(),
            new Akeneo\Platform\Bundle\InstallerBundle\PimInstallerBundle(),
            new Akeneo\Platform\Bundle\NotificationBundle\PimNotificationBundle(),
            new Akeneo\Platform\Bundle\UIBundle\PimUIBundle(),
            new Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\PimCatalogVolumeMonitoringBundle(),
        ];
    }

    /**
     * Bundles required by the PIM
     *
     * @return array
     */
    protected function getPimDependenciesBundles()
    {
        return [
            new Akeneo\Tool\Bundle\ConnectorBundle\PimConnectorBundle(),
            new Akeneo\Tool\Bundle\ClassificationBundle\AkeneoClassificationBundle(),
            new Akeneo\Tool\Bundle\VersioningBundle\AkeneoVersioningBundle(),
            new Akeneo\Tool\Bundle\ElasticsearchBundle\AkeneoElasticsearchBundle(),
            new Akeneo\Tool\Bundle\BatchBundle\AkeneoBatchBundle(),
            new Akeneo\Tool\Bundle\BatchQueueBundle\AkeneoBatchQueueBundle(),
            new Akeneo\Tool\Bundle\BufferBundle\AkeneoBufferBundle(),
            new Akeneo\Tool\Bundle\FileStorageBundle\AkeneoFileStorageBundle(),
            new Akeneo\Tool\Bundle\MeasureBundle\AkeneoMeasureBundle(),
            new Akeneo\Tool\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
            // PimApiBundle must be registered after FOSOAuthServerBundle
            new Akeneo\Tool\Bundle\ApiBundle\PimApiBundle(),
            new Oneup\FlysystemBundle\OneupFlysystemBundle(),
        ];
    }

    /**
     * Bundles coming from Symfony Standard Framework.
     *
     * @return array
     */
    protected function getSymfonyBundles()
    {
        return [
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\DoctrineCacheBundle\DoctrineCacheBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
        ];
    }

    /**
     * * Bundles required by Oro Platform
     *
     * @return array
     */
    protected function getOroDependencies()
    {
        return [
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
        ];
    }

    /**
     * Bundles coming from Oro Platform
     *
     * @return array
     */
    protected function getOroBundles()
    {
        return [
            new Oro\Bundle\AsseticBundle\OroAsseticBundle(),
            new Oro\Bundle\ConfigBundle\OroConfigBundle(),
            new Oro\Bundle\DataGridBundle\OroDataGridBundle(),
            new Oro\Bundle\FilterBundle\OroFilterBundle(),
            new Oro\Bundle\SecurityBundle\OroSecurityBundle(),
            new Oro\Bundle\TranslationBundle\OroTranslationBundle(),
        ];
    }

    /**
     * @return string
     */
    public function getRootDir(): string
    {
        return __DIR__;
    }

    /**
     * @return string
     */
    public function getCacheDir(): string
    {
        return dirname(__DIR__)
            . DIRECTORY_SEPARATOR
            . 'var'
            . DIRECTORY_SEPARATOR
            . 'cache'
            . DIRECTORY_SEPARATOR
            . $this->getEnvironment();
    }

    /**
     * @return string
     */
    public function getLogDir(): string
    {
        return dirname(__DIR__) . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'logs';
    }
}
