<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

class EntityConfigPass implements CompilerPassInterface
{
    const TAG_NAME = 'oro_entity_config.provider';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $configManagerDefinition = $container->getDefinition('oro_entity_config.config_manager');

        $tags = $container->findTaggedServiceIds(self::TAG_NAME);
        foreach ($tags as $id => $tag) {
            /** @var Definition $provider */
            $provider = $container->getDefinition($id);

            if (!isset($tag[0]['scope'])) {
                throw new RuntimeException(
                    sprintf("Tag '%s' for service '%s' doesn't have required param 'scope'", self::TAG_NAME, $id)
                );
            }

            if (!$container->hasDefinition('oro_entity_config.entity_config.' . $tag[0]['scope'])) {
                throw new RuntimeException(sprintf(
                    "Resources/config/entity_config.yml not found or has wrong 'scope'. Service '%s' with tag '%s' and tag-scope '%s' ",
                    $id, self::TAG_NAME, $tag[0]['scope']
                ));
            }

            $provider->setClass('Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider');
            $provider->setArguments(array(
                new Reference('oro_entity_config.config_manager'),
                new Reference('oro_entity_config.entity_config.' . $tag[0]['scope'])
            ));

            $configManagerDefinition->addMethodCall('addProvider', array($provider));
        }
    }
}
