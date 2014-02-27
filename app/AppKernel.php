<?php

use Symfony\Component\Config\Loader\LoaderInterface;
use Oro\Bundle\DistributionBundle\OroKernel;

/**
 * PIM AppKernel
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AppKernel extends OroKernel
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
        }

        $oroBundles = parent::registerBundles();
        $exclude = array(
            'Oro\Bundle\ImportExportBundle\OroImportExportBundle',
            'Oro\Bundle\QueryDesignerBundle\OroQueryDesignerBundle',
            'JMS\JobQueueBundle\JMSJobQueueBundle',
            'Oro\Bundle\AddressBundle\OroAddressBundle',
            'Oro\Bundle\SoapBundle\OroSoapBundle',
            'Oro\Bundle\SyncBundle\OroSyncBundle',
            'Oro\Bundle\WorkflowBundle\OroWorkflowBundle',
            'Oro\Bundle\NotificationBundle\OroNotificationBundle',
            'Oro\Bundle\CalendarBundle\OroCalendarBundle',
            'Oro\Bundle\CronBundle\OroCronBundle'
        );
        foreach ($oroBundles as $bundleIndex => $bundle) {
            if (in_array(get_class($bundle), $exclude)) {
                unset($oroBundles[$bundleIndex]);
            }
        }
        $bundles = array_merge($oroBundles, $bundles);

        $pimDepBundles = array(
            // Uncomment the following line to use MongoDB implementation
            // new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
            new APY\JsFormValidationBundle\APYJsFormValidationBundle(),
            new Akeneo\Bundle\MeasureBundle\AkeneoMeasureBundle(),
            new Akeneo\Bundle\BatchBundle\AkeneoBatchBundle()
        );
        $bundles = array_merge($bundles, $pimDepBundles);

        $pimBundles = array(
            // BAP overriden bundles
            new Pim\Bundle\NavigationBundle\PimNavigationBundle(),
            new Pim\Bundle\FilterBundle\PimFilterBundle(),
            new Pim\Bundle\UserBundle\PimUserBundle(),
            new Pim\Bundle\SearchBundle\PimSearchBundle(),
            new Pim\Bundle\JsFormValidationBundle\PimJsFormValidationBundle(),

            // PIM bundles
            new Pim\Bundle\DataGridBundle\PimDataGridBundle(),
            new Pim\Bundle\DashboardBundle\PimDashboardBundle(),
            new Pim\Bundle\InstallerBundle\PimInstallerBundle(),
            new Pim\Bundle\UIBundle\PimUIBundle(),
            new Pim\Bundle\FlexibleEntityBundle\PimFlexibleEntityBundle(),
            new Pim\Bundle\CatalogBundle\PimCatalogBundle(),
            new Pim\Bundle\TranslationBundle\PimTranslationBundle(),
            new Pim\Bundle\ImportExportBundle\PimImportExportBundle(),
            new Pim\Bundle\VersioningBundle\PimVersioningBundle(),
            new Pim\Bundle\CustomEntityBundle\PimCustomEntityBundle(),
            new Pim\Bundle\WebServiceBundle\PimWebServiceBundle(),
            new Pim\Bundle\EnrichBundle\PimEnrichBundle(),
            new Pim\Bundle\BaseConnectorBundle\PimBaseConnectorBundle(),
            new Pim\Bundle\TransformBundle\PimTransformBundle()
        );

        $bundles = array_merge($bundles, $pimBundles);

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
            $loader->load(__DIR__.'/config/mongodb/parameters_'.$this->getEnvironment().'.yml');
            $loader->load(__DIR__ .'/config/mongodb/config.yml');
        }
    }
}
