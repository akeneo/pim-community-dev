<?php

namespace Oro\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class AclAnnotationProviderPass implements CompilerPassInterface
{
    const PROVIDER_SERVICE_NAME = 'oro_security.acl.annotation_provider';
    const TAG_NAME = 'oro_security.acl.config_loader';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::PROVIDER_SERVICE_NAME)) {
            return;
        }

        $providerDef = $container->getDefinition(self::PROVIDER_SERVICE_NAME);

        $loaders = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach ($loaders as $id => $attributes) {
            $providerDef->addMethodCall('addLoader', [new Reference($id)]);
        }
    }
}
