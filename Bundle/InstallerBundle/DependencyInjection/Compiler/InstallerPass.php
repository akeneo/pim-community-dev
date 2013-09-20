<?php

namespace Oro\Bundle\InstallerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class InstallerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('installed') && $container->getParameter('installed')) {
            return;
        }

        $listener = $container->getDefinition('kernel.listener.install.event');

        $listener->addTag(
            'kernel.event_listener',
            array(
                'priority' => 10,
                'event'    => 'kernel.request',
                'method'   => 'onRequest'
            )
        );
    }
}