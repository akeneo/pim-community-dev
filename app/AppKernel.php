<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * PIM AppKernel
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
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
            new AcmeEnterprise\Bundle\AppBundle\AcmeEnterpriseAppBundle(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = $this->registerProjectBundles();

        if (in_array($this->getEnvironment(), ['dev', 'test', 'behat'])) {
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Symfony\Bundle\WebServerBundle\WebServerBundle();
        }

        if ('test_fake' === $this->getEnvironment()) {
            $bundles[] = new FriendsOfBehat\SymfonyExtension\Bundle\FriendsOfBehatSymfonyExtensionBundle();
        }

        $bundles = array_merge(
            $this->getSymfonyBundles(),
            $this->getOroDependencies(),
            $this->getOroBundles(),
            $this->getPimDependenciesBundles(),
            $this->getPimBundles(),
            $this->getPimEnterpriseBundles(),
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
     * Bundles coming from the PIM Enterprise Edition
     *
     * @return array
     */
    protected function getPimEnterpriseBundles()
    {
        return [
            new Akeneo\Tool\Bundle\FileMetadataBundle\AkeneoFileMetadataBundle(),
            new Akeneo\Tool\Bundle\FileTransformerBundle\AkeneoFileTransformerBundle(),
            new Akeneo\ReferenceEntity\Infrastructure\Symfony\AkeneoReferenceEntityBundle(),
            new Akeneo\Pim\Permission\Bundle\AkeneoPimPermissionBundle(),
            new Akeneo\Platform\Bundle\InstallerBundle\PimEnterpriseInstallerBundle(),
            new Akeneo\Asset\Bundle\AkeneoAssetBundle(),
            new Akeneo\Pim\Enrichment\Asset\Bundle\AkeneoPimEnrichmentAssetBundle(),
            new Akeneo\Pim\WorkOrganization\TeamworkAssistant\Bundle\AkeneoPimTeamworkAssistantBundle(),
            new Akeneo\Pim\WorkOrganization\ProductRevert\AkeneoPimProductRevertBundle(),
            new Akeneo\Pim\WorkOrganization\Workflow\Bundle\AkeneoPimWorkflowBundle(),
            new Akeneo\Pim\Automation\FranklinInsights\Infrastructure\Symfony\AkeneoFranklinInsightsBundle(),
            new Akeneo\Platform\Bundle\UIBundle\PimEnterpriseUIBundle(),
            new Hslavich\OneloginSamlBundle\HslavichOneloginSamlBundle(),
            new Akeneo\Platform\Bundle\AuthenticationBundle\AkeneoAuthenticationBundle(),
        ];
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
            new Akeneo\UserManagement\Bundle\PimUserBundle(),

            // Channel bundles
            new Akeneo\Channel\Bundle\AkeneoChannelBundle(),

            // PIM bundles
            new Akeneo\Pim\Enrichment\Bundle\AkeneoPimEnrichmentBundle(),
            new Akeneo\Pim\Structure\Bundle\AkeneoPimStructureBundle(),
            new Akeneo\Tool\Bundle\ClassificationBundle\AkeneoClassificationBundle(),
            new Akeneo\Tool\Bundle\RuleEngineBundle\AkeneoRuleEngineBundle(),
            new Akeneo\Platform\Bundle\AnalyticsBundle\PimAnalyticsBundle(),
            new Akeneo\Tool\Bundle\ApiBundle\PimApiBundle(),
            new Akeneo\Platform\Bundle\CatalogVolumeMonitoringBundle\PimCatalogVolumeMonitoringBundle(),
            new Akeneo\Tool\Bundle\ConnectorBundle\PimConnectorBundle(),
            new Akeneo\Platform\Bundle\DashboardBundle\PimDashboardBundle(),
            new Oro\Bundle\PimDataGridBundle\PimDataGridBundle(),
            new Akeneo\Platform\Bundle\ImportExportBundle\PimImportExportBundle(),
            new Akeneo\Platform\Bundle\InstallerBundle\PimInstallerBundle(),
            new Akeneo\Platform\Bundle\NotificationBundle\PimNotificationBundle(),
            new Akeneo\Platform\Bundle\UIBundle\PimUIBundle(),
            new Akeneo\Tool\Bundle\VersioningBundle\AkeneoVersioningBundle(),
            new Akeneo\Pim\Automation\RuleEngine\Bundle\AkeneoPimRuleEngineBundle(),
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
            new Akeneo\Tool\Bundle\ElasticsearchBundle\AkeneoElasticsearchBundle(),
            new Akeneo\Tool\Bundle\BatchBundle\AkeneoBatchBundle(),
            new Akeneo\Tool\Bundle\BatchQueueBundle\AkeneoBatchQueueBundle(),
            new Akeneo\Tool\Bundle\BufferBundle\AkeneoBufferBundle(),
            new Akeneo\Tool\Bundle\FileStorageBundle\AkeneoFileStorageBundle(),
            new Akeneo\Tool\Bundle\MeasureBundle\AkeneoMeasureBundle(),
            new Akeneo\Tool\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new FOS\OAuthServerBundle\FOSOAuthServerBundle(),
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
