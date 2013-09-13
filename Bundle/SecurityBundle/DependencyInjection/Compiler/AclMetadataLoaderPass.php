<?php

namespace Oro\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class AclMetadataLoaderPass implements CompilerPassInterface
{
    const ACL_ANNOTATION_METADATA_PROVIDER = 'oro_security.acl.annotation_provider';
    const ACL_ANNOTATION_METADATA_PROVIDER_TAG = 'oro_security.acl.metadata_loader';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::ACL_ANNOTATION_METADATA_PROVIDER)) {

            return;
        }

        $providerDef = $container->getDefinition(self::ACL_ANNOTATION_METADATA_PROVIDER);
        $loaders = $container->findTaggedServiceIds(self::ACL_ANNOTATION_METADATA_PROVIDER_TAG);

        foreach ($loaders as $id => $attributes) {
            $providerDef->addMethodCall(
                'addLoader',
                array(new Reference($id))
            );
        }
    }
}

