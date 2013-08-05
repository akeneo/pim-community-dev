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
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),

            // BAP deps
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\JobQueueBundle\JMSJobQueueBundle(),
            new JMS\SerializerBundle\JMSSerializerBundle($this),
            new Knp\Bundle\PaginatorBundle\KnpPaginatorBundle(),
            new FOS\JsRoutingBundle\FOSJsRoutingBundle(),
            new FOS\RestBundle\FOSRestBundle(),
            new Nelmio\ApiDocBundle\NelmioApiDocBundle(),
            new BeSimple\SoapBundle\BeSimpleSoapBundle(),
            new Stof\DoctrineExtensionsBundle\StofDoctrineExtensionsBundle(),
            new Knp\Bundle\MenuBundle\KnpMenuBundle(),
            new Escape\WSSEAuthenticationBundle\EscapeWSSEAuthenticationBundle(),
            new Liip\ImagineBundle\LiipImagineBundle(),
            new Bazinga\ExposeTranslationBundle\BazingaExposeTranslationBundle(),
            new APY\JsFormValidationBundle\APYJsFormValidationBundle(),
            new Genemu\Bundle\FormBundle\GenemuFormBundle(),

            // BAP bundles
            new Oro\Bundle\FlexibleEntityBundle\OroFlexibleEntityBundle(),
            new Oro\Bundle\UIBundle\OroUIBundle(),
            new Oro\Bundle\JsFormValidationBundle\OroJsFormValidationBundle(),
            new Oro\Bundle\SoapBundle\OroSoapBundle(),
            new Oro\Bundle\SearchBundle\OroSearchBundle(),
            new Oro\Bundle\UserBundle\OroUserBundle(),
            new Oro\Bundle\MeasureBundle\OroMeasureBundle(),
            new Oro\Bundle\SegmentationTreeBundle\OroSegmentationTreeBundle(),
            new Oro\Bundle\NavigationBundle\OroNavigationBundle(),
            new Oro\Bundle\ConfigBundle\OroConfigBundle(),
            new Oro\Bundle\FilterBundle\OroFilterBundle(),
            new Oro\Bundle\GridBundle\OroGridBundle(),
            new Oro\Bundle\WindowsBundle\OroWindowsBundle(),
            new Oro\Bundle\DataAuditBundle\OroDataAuditBundle(),
            new Oro\Bundle\FormBundle\OroFormBundle(),
            new Oro\Bundle\TagBundle\OroTagBundle(),
            new Oro\Bundle\AsseticBundle\OroAsseticBundle(),
            new Oro\Bundle\TranslationBundle\OroTranslationBundle(),
            new Oro\Bundle\OrganizationBundle\OroOrganizationBundle(),
            new Oro\Bundle\NotificationBundle\OroNotificationBundle($this),
            new Oro\Bundle\EmailBundle\OroEmailBundle(),
            new Oro\Bundle\EntityBundle\OroEntityBundle(),
            new Oro\Bundle\EntityConfigBundle\OroEntityConfigBundle(),
            new Oro\Bundle\EntityExtendBundle\OroEntityExtendBundle(),
            new Oro\Bundle\ImapBundle\OroImapBundle(),

            // PIM deps
            new Knp\Bundle\GaufretteBundle\KnpGaufretteBundle(),

            // BAP overrided bundles
            new Pim\Bundle\NavigationBundle\PimNavigationBundle(),
            new Pim\Bundle\FilterBundle\PimFilterBundle(),
            new Pim\Bundle\GridBundle\PimGridBundle(),
            new Pim\Bundle\UserBundle\PimUserBundle(),
            new Pim\Bundle\SearchBundle\PimSearchBundle(),

            // PIM bundles
            new Pim\Bundle\ConfigBundle\PimConfigBundle(),
            new Pim\Bundle\InstallerBundle\PimInstallerBundle(),
            new Pim\Bundle\UIBundle\PimUIBundle(),
            new Pim\Bundle\ProductBundle\PimProductBundle(),
            new Pim\Bundle\TranslationBundle\PimTranslationBundle(),
            new Pim\Bundle\JsFormValidationBundle\PimJsFormValidationBundle(),
            new Pim\Bundle\BatchBundle\PimBatchBundle(),
            new Pim\Bundle\ImportExportBundle\PimImportExportBundle(),
            new Pim\Bundle\DemoBundle\PimDemoBundle()
        );

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
    }
}
