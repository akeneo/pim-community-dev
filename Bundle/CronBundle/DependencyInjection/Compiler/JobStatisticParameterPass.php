<?php

namespace Oro\Bundle\CronBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class JobStatisticParameterPass implements CompilerPassInterface
{
    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $isInstalled = $container->getParameter('installed');
        $configValue = $container->getParameter('oro_cron.jms_statistics');

        $statisticEnabled = $isInstalled ? $configValue : false;
        $container->setParameter('jms_job_queue.statistics', $statisticEnabled);
    }
}
