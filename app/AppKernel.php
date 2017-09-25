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
            new Akeneo\Bundle\FileMetadataBundle\AkeneoFileMetadataBundle(),
            new Akeneo\Bundle\FileTransformerBundle\AkeneoFileTransformerBundle(),
            new PimEnterprise\Bundle\CatalogBundle\PimEnterpriseCatalogBundle(),
            new PimEnterprise\Bundle\ConnectorBundle\PimEnterpriseConnectorBundle(),
            new PimEnterprise\Bundle\DashboardBundle\PimEnterpriseDashboardBundle(),
            new PimEnterprise\Bundle\DataGridBundle\PimEnterpriseDataGridBundle(),
            new PimEnterprise\Bundle\EnrichBundle\PimEnterpriseEnrichBundle(),
            new PimEnterprise\Bundle\FilterBundle\PimEnterpriseFilterBundle(),
            new PimEnterprise\Bundle\ImportExportBundle\PimEnterpriseImportExportBundle(),
            new PimEnterprise\Bundle\InstallerBundle\PimEnterpriseInstallerBundle(),
            new PimEnterprise\Bundle\PdfGeneratorBundle\PimEnterprisePdfGeneratorBundle(),
            new PimEnterprise\Bundle\ProductAssetBundle\PimEnterpriseProductAssetBundle(),
            new PimEnterprise\Bundle\SecurityBundle\PimEnterpriseSecurityBundle(),
            new PimEnterprise\Bundle\TeamworkAssistantBundle\PimEnterpriseTeamworkAssistantBundle(),
            new PimEnterprise\Bundle\UIBundle\PimEnterpriseUIBundle(),
            new PimEnterprise\Bundle\UserBundle\PimEnterpriseUserBundle(),
            new PimEnterprise\Bundle\VersioningBundle\PimEnterpriseVersioningBundle(),
            new PimEnterprise\Bundle\WorkflowBundle\PimEnterpriseWorkflowBundle(),
            new PimEnterprise\Bundle\ApiBundle\PimEnterpriseApiBundle(),
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
            new Pim\Bundle\NavigationBundle\PimNavigationBundle(),
            new Pim\Bundle\FilterBundle\PimFilterBundle(),
            new Pim\Bundle\UserBundle\PimUserBundle(),

            // PIM bundles
            new Akeneo\Bundle\ClassificationBundle\AkeneoClassificationBundle(),
            new Akeneo\Bundle\RuleEngineBundle\AkeneoRuleEngineBundle(),
            new Pim\Bundle\AnalyticsBundle\PimAnalyticsBundle(),
            new Pim\Bundle\ApiBundle\PimApiBundle(),
            new Pim\Bundle\CatalogBundle\PimCatalogBundle(),
            new Pim\Bundle\CommentBundle\PimCommentBundle(),
            new Pim\Bundle\ConnectorBundle\PimConnectorBundle(),
            new Pim\Bundle\DashboardBundle\PimDashboardBundle(),
            new Pim\Bundle\DataGridBundle\PimDataGridBundle(),
            new Pim\Bundle\EnrichBundle\PimEnrichBundle(),
            new Pim\Bundle\ImportExportBundle\PimImportExportBundle(),
            new Pim\Bundle\InstallerBundle\PimInstallerBundle(),
            new Pim\Bundle\LocalizationBundle\PimLocalizationBundle(),
            new Pim\Bundle\NotificationBundle\PimNotificationBundle(),
            new Pim\Bundle\PdfGeneratorBundle\PimPdfGeneratorBundle(),
            new Pim\Bundle\ReferenceDataBundle\PimReferenceDataBundle(),
            new Pim\Bundle\UIBundle\PimUIBundle(),
            new Pim\Bundle\VersioningBundle\PimVersioningBundle(),
            new PimEnterprise\Bundle\CatalogRuleBundle\PimEnterpriseCatalogRuleBundle(),
            new PimEnterprise\Bundle\ReferenceDataBundle\PimEnterpriseReferenceDataBundle(),
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
            new Akeneo\Bundle\ElasticsearchBundle\AkeneoElasticsearchBundle(),
            new Akeneo\Bundle\BatchBundle\AkeneoBatchBundle(),
            new Akeneo\Bundle\BatchQueueBundle\AkeneoBatchQueueBundle(),
            new Akeneo\Bundle\BufferBundle\AkeneoBufferBundle(),
            new Akeneo\Bundle\FileStorageBundle\AkeneoFileStorageBundle(),
            new Akeneo\Bundle\MeasureBundle\AkeneoMeasureBundle(),
            new Akeneo\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle(),
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
            new Escape\WSSEAuthenticationBundle\EscapeWSSEAuthenticationBundle(),
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
            new Oro\Bundle\UserBundle\OroUserBundle(),
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
