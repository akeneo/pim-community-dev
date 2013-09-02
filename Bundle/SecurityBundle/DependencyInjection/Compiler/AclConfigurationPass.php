<?php

namespace Oro\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;

class AclConfigurationPass implements CompilerPassInterface
{
    const NEW_ACL_VOTER_CLASS = 'oro_security.acl.voter.class';
    const NEW_ACL_PROVIDER = 'oro_security.acl.provider';
    const NEW_ACL_PERMISSION_GRANTING_STRATEGY = 'oro_security.acl.permission_granting_strategy';
    const NEW_ACL_PERMISSION_MAP = 'oro_security.acl.permission_map';
    const NEW_ACL_OBJECT_ID_STRATEGY = 'oro_security.acl.object_identity_retrieval_strategy';

    const DEFAULT_ACL_VOTER = 'security.acl.voter.basic_permissions';
    const DEFAULT_ACL_PROVIDER = 'security.acl.dbal.provider';

    const ACL_EXTENSION_SELECTOR = 'oro_security.acl.extension_selector';
    const ACL_EXTENSION_TAG = 'oro_security.acl.extension';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->configureAclExtensionSelector($container);
        $this->configureDefaultAclProvider($container);
        $this->configureDefaultAclVoter($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function configureAclExtensionSelector(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::ACL_EXTENSION_SELECTOR)) {
            $selectorDef = $container->getDefinition(self::ACL_EXTENSION_SELECTOR);
            $extensions = $this->loadAclExtensions($container);
            foreach ($extensions as $extensionServiceId) {
                $selectorDef->addMethodCall('addAclExtension', array(new Reference($extensionServiceId)));
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function configureDefaultAclProvider(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::DEFAULT_ACL_PROVIDER)) {
            $providerDef = $container->getDefinition(self::DEFAULT_ACL_PROVIDER);
            // substitute the ACL Permission Granting Strategy
            if ($container->hasDefinition(self::NEW_ACL_PERMISSION_GRANTING_STRATEGY)) {
                $providerDef->replaceArgument(1, new Reference(self::NEW_ACL_PERMISSION_GRANTING_STRATEGY));
            }
        }
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function configureDefaultAclVoter(ContainerBuilder $container)
    {
        if ($container->hasDefinition(self::DEFAULT_ACL_VOTER)) {
            $voterDef = $container->getDefinition(self::DEFAULT_ACL_VOTER);
            if ($container->hasParameter(self::NEW_ACL_VOTER_CLASS)) {
                // substitute the class of the default ACL Voter
                $voterDef->setClass($container->getParameter(self::NEW_ACL_VOTER_CLASS));
                // make the ACL Voter as a context for the ACL Permission Granting Strategy
                if ($container->hasDefinition(self::NEW_ACL_PERMISSION_GRANTING_STRATEGY)) {
                    $newStrategyDef = $container->getDefinition(self::NEW_ACL_PERMISSION_GRANTING_STRATEGY);
                    $newStrategyDef->addMethodCall('setContext', array($voterDef));
                }
                // inject ACL Extension Selector into the ACL Voter
                if ($container->hasDefinition(self::ACL_EXTENSION_SELECTOR)) {
                    $selectorDef = $container->getDefinition(self::ACL_EXTENSION_SELECTOR);
                    $voterDef->addMethodCall('setAclExtensionSelector', array($selectorDef));
                }
            }
            // substitute the ACL Provider and set the default ACL Provider as a base provider for new ACL Provider
            if ($container->hasDefinition(self::NEW_ACL_PROVIDER)) {
                $newProviderDef = $container->getDefinition(self::NEW_ACL_PROVIDER);
                $newProviderDef->addMethodCall('setBaseAclProvider', array($voterDef->getArgument(0)));
                $voterDef->replaceArgument(0, new Reference(self::NEW_ACL_PROVIDER));
            }
            // substitute ACL Object Identity Retrieval Strategy
            if ($container->hasDefinition(self::NEW_ACL_OBJECT_ID_STRATEGY)) {
                $voterDef->replaceArgument(1, new Reference(self::NEW_ACL_OBJECT_ID_STRATEGY));
            }
            // substitute ACL Permission Map
            if ($container->hasDefinition(self::NEW_ACL_PERMISSION_MAP)) {
                $voterDef->replaceArgument(3, new Reference(self::NEW_ACL_PERMISSION_MAP));
            }
        }
    }

    /**
     * Load ACL extensions and sort them by priority.
     *
     * @param ContainerBuilder $container
     * @return array
     */
    protected function loadAclExtensions(ContainerBuilder $container)
    {
        $extensions = array();
        foreach ($container->findTaggedServiceIds(self::ACL_EXTENSION_TAG) as $id => $attributes) {
            $priority = 0;
            foreach ($attributes as $attr) {
                if (isset($attr['priority'])) {
                    $priority = (int)$attr['priority'];
                    break;
                }
            }

            $extensions[] = array('id' => $id, 'priority' => $priority);
        }
        usort(
            $extensions,
            function ($a, $b) {
                return $a['priority'] == $b['priority']
                    ? 0
                    : ($a['priority'] < $b['priority']) ? -1 : 1;
            }
        );

        return array_map(
            function ($el) {
                return $el['id'];
            },
            $extensions
        );
    }
}
