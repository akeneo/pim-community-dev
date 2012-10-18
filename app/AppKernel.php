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
            new Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle(),
            new JMS\AopBundle\JMSAopBundle(),
            new JMS\DiExtraBundle\JMSDiExtraBundle($this),
            new JMS\SecurityExtraBundle\JMSSecurityExtraBundle(),
            // MongoDB storage
            new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle(),
            // To set up basic data
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            // translatable, timestampable, etc

            // BAP modules

            // PIM modules
            new Strixos\CoreBundle\StrixosCoreBundle(),
            new Strixos\WidgetBundle\StrixosWidgetBundle(),
            new Strixos\CatalogBundle\StrixosCatalogBundle(),
            new Strixos\ReportBundle\StrixosReportBundle(),
            new Strixos\DataFlowBundle\StrixosDataFlowBundle(),
            new Strixos\DashboardBundle\StrixosDashboardBundle(),
            new Strixos\IcecatConnectorBundle\StrixosIcecatConnectorBundle(),
            new Bap\FlexibleEntityBundle\BapFlexibleEntityBundle(),
            new Akeneo\CatalogBundle\AkeneoCatalogBundle(),

            // community bundles
            new APY\DataGridBundle\APYDataGridBundle(),
            new FOS\UserBundle\FOSUserBundle(),

            //new Nidup\TestBundle\NidupTestBundle(),
            new Pim\Bundle\UserBundle\PimUserBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
