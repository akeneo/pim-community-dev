<?php

namespace Oro\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

class AclConfigurationPass implements CompilerPassInterface
{
    const NEW_ACL_PROVIDER = 'oro_security.acl.provider';
    const NEW_ACL_OBJECT_ID_STRATEGY = 'oro_security.acl.object_identity_retrieval_strategy';
    const NEW_ACL_PERMISSION_GRANTING_STRATEGY = 'oro_security.acl.permission_granting_strategy';
    const NEW_ACL_PERMISSION_MAP = 'oro_security.acl.permission.map';

    const ACL_VOTER = 'security.acl.voter.basic_permissions';
    const ACL_PROVIDER = 'security.acl.dbal.provider';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        // configure security.acl.dbal.provider
        if ($container->hasDefinition(self::ACL_PROVIDER)) {
            $providerDef = $container->getDefinition(self::ACL_PROVIDER);
            // set security.acl.permission_granting_strategy argument
            if ($container->hasDefinition(self::NEW_ACL_PERMISSION_GRANTING_STRATEGY)) {
                $providerDef->replaceArgument(1, new Reference(self::NEW_ACL_PERMISSION_GRANTING_STRATEGY));
            }
        }
        // configure security.acl.voter.basic_permissions
        if ($container->hasDefinition(self::ACL_VOTER)) {
            $voterDef = $container->getDefinition(self::ACL_VOTER);
            // set security.acl.provider argument and ser base provider for oro_security.acl.provider
            if ($container->hasDefinition(self::NEW_ACL_PROVIDER)) {
                $newProviderDef = $container->getDefinition(self::NEW_ACL_PROVIDER);
                $newProviderDef->addMethodCall('setBaseAclProvider', array($voterDef->getArgument(0)));
                $voterDef->replaceArgument(0, new Reference(self::NEW_ACL_PROVIDER));
            }
            // set security.acl.object_identity_retrieval_strategy argument
            if ($container->hasDefinition(self::NEW_ACL_OBJECT_ID_STRATEGY)) {
                $voterDef->replaceArgument(1, new Reference(self::NEW_ACL_OBJECT_ID_STRATEGY));
            }
            // set security.acl.permission.map argument
            if ($container->hasDefinition(self::NEW_ACL_PERMISSION_MAP)) {
                $voterDef->replaceArgument(3, new Reference(self::NEW_ACL_PERMISSION_MAP));
            }
        }
    }
}
