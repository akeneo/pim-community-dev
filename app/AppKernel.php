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
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundles = [
            // your app bundles should be registered here
            new AcmeEnterprise\Bundle\AppBundle\AcmeEnterpriseAppBundle()
        ];

        if (in_array($this->getEnvironment(), ['dev', 'test', 'behat'])) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
            $bundles[] = new Symfony\Bundle\DebugBundle\DebugBundle();
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
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');

        if (is_file($file = __DIR__.'/config/config_'.$this->getEnvironment().'_local.yml')) {
            $loader->load($file);
        }

        if (isset($this->bundleMap['DoctrineMongoDBBundle'])) {
            $loader->load(__DIR__ .'/config/config_mongodb.yml');
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
            new PimEnterprise\Bundle\CatalogBundle\PimEnterpriseCatalogBundle(),
            new PimEnterprise\Bundle\EnrichBundle\PimEnterpriseEnrichBundle(),
            new PimEnterprise\Bundle\DashboardBundle\PimEnterpriseDashboardBundle(),
            new PimEnterprise\Bundle\SecurityBundle\PimEnterpriseSecurityBundle(),
            new PimEnterprise\Bundle\WorkflowBundle\PimEnterpriseWorkflowBundle(),
            new PimEnterprise\Bundle\BaseConnectorBundle\PimEnterpriseBaseConnectorBundle(),
            new PimEnterprise\Bundle\InstallerBundle\PimEnterpriseInstallerBundle(),
            new PimEnterprise\Bundle\DataGridBundle\PimEnterpriseDataGridBundle(),
            new PimEnterprise\Bundle\FilterBundle\PimEnterpriseFilterBundle(),
            new PimEnterprise\Bundle\UserBundle\PimEnterpriseUserBundle(),
            new PimEnterprise\Bundle\ImportExportBundle\PimEnterpriseImportExportBundle(),
            new PimEnterprise\Bundle\UIBundle\PimEnterpriseUIBundle(),
            new PimEnterprise\Bundle\VersioningBundle\PimEnterpriseVersioningBundle(),
            new PimEnterprise\Bundle\WebServiceBundle\PimEnterpriseWebServiceBundle(),
            new PimEnterprise\Bundle\PdfGeneratorBundle\PimEnterprisePdfGeneratorBundle(),
            new PimEnterprise\Bundle\LocalizationBundle\PimEnterpriseLocalizationBundle(),

            new Akeneo\Bundle\FileMetadataBundle\AkeneoFileMetadataBundle(),
            new Akeneo\Bundle\FileTransformerBundle\AkeneoFileTransformerBundle(),
            new PimEnterprise\Bundle\ProductAssetBundle\PimEnterpriseProductAssetBundle(),
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
            new Pim\Bundle\JsFormValidationBundle\PimJsFormValidationBundle(),

            // PIM bundles
            new Pim\Bundle\AnalyticsBundle\PimAnalyticsBundle(),
            new Pim\Bundle\DashboardBundle\PimDashboardBundle(),
            new Pim\Bundle\InstallerBundle\PimInstallerBundle(),
            new Pim\Bundle\UIBundle\PimUIBundle(),
            new Pim\Bundle\NotificationBundle\PimNotificationBundle(),
            new Pim\Bundle\CatalogBundle\PimCatalogBundle(),
            new Pim\Bundle\DataGridBundle\PimDataGridBundle(),
            new Pim\Bundle\TranslationBundle\PimTranslationBundle(),
            new Pim\Bundle\ImportExportBundle\PimImportExportBundle(),
            new Pim\Bundle\VersioningBundle\PimVersioningBundle(),
            new Pim\Bundle\WebServiceBundle\PimWebServiceBundle(),
            new Pim\Bundle\EnrichBundle\PimEnrichBundle(),
            new Pim\Bundle\BaseConnectorBundle\PimBaseConnectorBundle(),
            new Pim\Bundle\TransformBundle\PimTransformBundle(),
            new Pim\Bundle\CommentBundle\PimCommentBundle(),
            new Pim\Bundle\PdfGeneratorBundle\PimPdfGeneratorBundle(),
            new Akeneo\Bundle\RuleEngineBundle\AkeneoRuleEngineBundle(),
            new PimEnterprise\Bundle\CatalogRuleBundle\PimEnterpriseCatalogRuleBundle(),
            new Pim\Bundle\ReferenceDataBundle\PimReferenceDataBundle(),
            new PimEnterprise\Bundle\ReferenceDataBundle\PimEnterpriseReferenceDataBundle(),
            new Pim\Bundle\ConnectorBundle\PimConnectorBundle(),
            new Pim\Bundle\LocalizationBundle\PimLocalizationBundle(),
            new Akeneo\Bundle\ClassificationBundle\AkeneoClassificationBundle(),
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
            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            new APY\JsFormValidationBundle\APYJsFormValidationBundle(),
            new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle(),
            new Oneup\FlysystemBundle\OneupFlysystemBundle(),
            new Akeneo\Bundle\MeasureBundle\AkeneoMeasureBundle(),
            new Akeneo\Bundle\BatchBundle\AkeneoBatchBundle(),
            new Akeneo\Bundle\BufferBundle\AkeneoBufferBundle(),
            new Akeneo\Bundle\StorageUtilsBundle\AkeneoStorageUtilsBundle(),
            new Akeneo\Bundle\FileStorageBundle\AkeneoFileStorageBundle(),
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
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            // Uncomment the following line to use MongoDB implementation
            // new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
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
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
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
            new Oro\Bundle\UIBundle\OroUIBundle(),
            new Oro\Bundle\AsseticBundle\OroAsseticBundle(),
            new Oro\Bundle\ConfigBundle\OroConfigBundle(),
            new Oro\Bundle\DataGridBundle\OroDataGridBundle(),
            new Oro\Bundle\FilterBundle\OroFilterBundle(),
            new Oro\Bundle\FormBundle\OroFormBundle(),
            new Oro\Bundle\NavigationBundle\OroNavigationBundle(),
            new Oro\Bundle\RequireJSBundle\OroRequireJSBundle(),
            new Oro\Bundle\SecurityBundle\OroSecurityBundle(),
            new Oro\Bundle\TranslationBundle\OroTranslationBundle(),
            new Oro\Bundle\UserBundle\OroUserBundle(),
        ];
    }
}
