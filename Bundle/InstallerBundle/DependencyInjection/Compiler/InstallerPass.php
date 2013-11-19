<?php

namespace Oro\Bundle\InstallerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class InstallerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // we have to add installer listener even if the application is already installed
        // this is required because we are clearing the cache on the last installation step
        // and as the result the login page is appeared instead of the final installer page
        //if ($container->hasParameter('installed') && $container->getParameter('installed')) {
        //    return;
        //}

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
