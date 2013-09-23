<?php

namespace Oro\Bundle\EmailBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class EmailBodyLoaderPass implements CompilerPassInterface
{
    const SERVICE_KEY = 'oro_email.email_body_loader_selector';
    const TAG = 'oro_email.email_body_loader';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_KEY)) {
            return;
        }

        $selectorDef = $container->getDefinition(self::SERVICE_KEY);
        $taggedServices = $container->findTaggedServiceIds(self::TAG);
        foreach ($taggedServices as $loaderServiceId => $tagAttributes) {
            $selectorDef->addMethodCall('addLoader', array(new Reference($loaderServiceId)));
        }
    }
}
