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
        $bundles = array();

        if (in_array($this->getEnvironment(), array('dev', 'test', 'behat'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
//            $bundles[] = new Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle();
        }

        $bundles = array_merge(
            $bundles,
            $this->getSymfonyBundles(),
            $this->getOroDependencies(),
            $this->getOroBundles(),
            $this->getPimDependenciesBundles(),
            $this->getPimBundles(),
            $this->getPimEnterpriseBundles()
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
            new PimEnterprise\Bundle\TransformBundle\PimEnterpriseTransformBundle(),
            new PimEnterprise\Bundle\PdfGeneratorBundle\PimEnterprisePdfGeneratorBundle(),
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
            new Pim\Bundle\EntityBundle\PimEntityBundle(),

            // PIM bundles
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
            new PimEnterprise\Bundle\RuleEngineBundle\PimEnterpriseRuleEngineBundle(),
            new PimEnterprise\Bundle\CatalogRuleBundle\PimEnterpriseCatalogRuleBundle(),
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
            new Akeneo\Bundle\MeasureBundle\AkeneoMeasureBundle(),
            new Akeneo\Bundle\BatchBundle\AkeneoBatchBundle()
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
            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
            new BeSimple\SoapBundle\BeSimpleSoapBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new Escape\WSSEAuthenticationBundle\EscapeWSSEAuthenticationBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new Genemu\Bundle\FormBundle\GenemuFormBundle(),
            new JDare\ClankBundle\JDareClankBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new Lexik\Bundle\MaintenanceBundle\LexikMaintenanceBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Sylius\Bundle\FlowBundle\SyliusFlowBundle(),

            // the following bundles are disabled by the PIM
            //
            // new JMS\JobQueueBundle\JMSJobQueueBundle(),
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
            new Oro\Bundle\DataAuditBundle\OroDataAuditBundle(),
            new Oro\Bundle\DataGridBundle\OroDataGridBundle(),
            new Oro\Bundle\DistributionBundle\OroDistributionBundle(),
            new Oro\Bundle\EmailBundle\OroEmailBundle(),
            new Oro\Bundle\EntityBundle\OroEntityBundle(),
            new Oro\Bundle\EntityConfigBundle\OroEntityConfigBundle(),
            new Oro\Bundle\EntityExtendBundle\OroEntityExtendBundle($this),
            new Oro\Bundle\FilterBundle\OroFilterBundle(),
            new Oro\Bundle\FormBundle\OroFormBundle(),
            new Oro\Bundle\HelpBundle\OroHelpBundle(),
            new Oro\Bundle\ImapBundle\OroImapBundle(),
            new Oro\Bundle\InstallerBundle\OroInstallerBundle(),
            new Oro\Bundle\LocaleBundle\OroLocaleBundle(),
            new Oro\Bundle\NavigationBundle\OroNavigationBundle(),
            new Oro\Bundle\OrganizationBundle\OroOrganizationBundle(),
            new Oro\Bundle\PlatformBundle\OroPlatformBundle(),
            new Oro\Bundle\RequireJSBundle\OroRequireJSBundle(),
            new Oro\Bundle\SecurityBundle\OroSecurityBundle(),
            new Oro\Bundle\TranslationBundle\OroTranslationBundle(),
            new Oro\Bundle\UserBundle\OroUserBundle(),
            new Oro\Bundle\WindowsBundle\OroWindowsBundle(),

            // the following bundles are disabled by the PIM
            //
            // new Oro\Bundle\AddressBundle\OroAddressBundle(),
            // new Oro\Bundle\CalendarBundle\OroCalendarBundle(),
            // new Oro\Bundle\CronBundle\OroCronBundle(),
            // new Oro\Bundle\NotificationBundle\OroNotificationBundle(),
            // new Oro\Bundle\QueryDesignerBundle\OroQueryDesignerBundle(),
            // new Oro\Bundle\SearchBundle\OroSearchBundle(),
            // new Oro\Bundle\SoapBundle\OroSoapBundle(),
            // new Oro\Bundle\SyncBundle\OroSyncBundle(),
            // new Oro\Bundle\TagBundle\OroTagBundle(),
            // new Oro\Bundle\WorkflowBundle\OroWorkflowBundle(),
        ];
    }
}
