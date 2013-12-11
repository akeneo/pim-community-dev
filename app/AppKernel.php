<?php

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;

class AppKernel extends Kernel
{
    public function registerBundles()
    {
        $bundles = array(
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Symfony\Bundle\MonologBundle\MonologBundle(),
            new Symfony\Bundle\SwiftmailerBundle\SwiftmailerBundle(),
            new Symfony\Bundle\AsseticBundle\AsseticBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            // Uncomment the following line to use MongoDB implementation
            // new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            // BAP deps
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new BeSimple\SoapBundle\BeSimpleSoapBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Escape\WSSEAuthenticationBundle\EscapeWSSEAuthenticationBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Bazinga\ExposeTranslationBundle\BazingaExposeTranslationBundle(),
            new APY\JsFormValidationBundle\APYJsFormValidationBundle(),
            new Genemu\Bundle\FormBundle\GenemuFormBundle(),
            new A2lix\TranslationFormBundle\A2lixTranslationFormBundle(),
            new JDare\ClankBundle\JDareClankBundle(),
            new Lexik\Bundle\MaintenanceBundle\LexikMaintenanceBundle(),
            new Sylius\Bundle\FlowBundle\SyliusFlowBundle(),

            // PIM deps
            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),
        );

        // BAP bundles
        $bundles = array_merge($bundles, Oro\Bundle\PlatformBundle\OroPlatformBundle::registeredBundles($this));

        $pimBundles = array(
            // BAP overriden bundles
            new Pim\Bundle\NavigationBundle\PimNavigationBundle(),
            new Pim\Bundle\FilterBundle\PimFilterBundle(),
            new Pim\Bundle\GridBundle\PimGridBundle(),
            new Pim\Bundle\UserBundle\PimUserBundle(),
            new Pim\Bundle\SearchBundle\PimSearchBundle(),
            new Pim\Bundle\JsFormValidationBundle\PimJsFormValidationBundle(),
            new Pim\Bundle\DataAuditBundle\PimDataAuditBundle(),

            // PIM bundles
            new Pim\Bundle\DashboardBundle\PimDashboardBundle(),
            new Pim\Bundle\InstallerBundle\PimInstallerBundle(),
            new Pim\Bundle\UIBundle\PimUIBundle(),
            new Pim\Bundle\FlexibleEntityBundle\PimFlexibleEntityBundle(),
            new Pim\Bundle\CatalogBundle\PimCatalogBundle(),
            new Pim\Bundle\TranslationBundle\PimTranslationBundle(),
            new Pim\Bundle\ImportExportBundle\PimImportExportBundle(),
            new Pim\Bundle\DemoBundle\PimDemoBundle(),
            new Pim\Bundle\VersioningBundle\PimVersioningBundle(),
            new Pim\Bundle\CustomEntityBundle\PimCustomEntityBundle(),
        );

        $bundles = array_merge($bundles, $pimBundles);

        if (in_array($this->getEnvironment(), array('dev', 'test', 'behat'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

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
