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

            // BAP modules
            new Oro\Bundle\FlexibleEntityBundle\OroFlexibleEntityBundle(),
            new Oro\Bundle\UIBundle\OroUIBundle(),

            // PIM modules
            new Pim\Bundle\UIBundle\PimUIBundle(),
            new Pim\Bundle\DashboardBundle\PimDashboardBundle(),
            new Pim\Bundle\FlexibleProductBundle\PimFlexibleProductBundle(),

            // Community bundles
            new APY\DataGridBundle\APYDataGridBundle(),
        );

        if (in_array($this->getEnvironment(), array('dev', 'test'))) {
            $bundles[] = new Symfony\Bundle\WebProfilerBundle\WebProfilerBundle();
            $bundles[] = new Sensio\Bundle\DistributionBundle\SensioDistributionBundle();
            $bundles[] = new Sensio\Bundle\GeneratorBundle\SensioGeneratorBundle();

            // Test bundle only for Unit test
            $bundles[] = new Pim\Bundle\TestBundle\PimTestBundle();
        }

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yml');
    }
}
