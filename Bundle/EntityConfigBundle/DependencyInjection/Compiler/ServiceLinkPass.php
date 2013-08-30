<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

class ServiceLinkPass implements CompilerPassInterface
{
    const TAG_NAME = 'oro_service_link';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $tags = $container->findTaggedServiceIds(self::TAG_NAME);

        foreach ($tags as $id => $tag) {
            /** @var Definition $service */
            $service = $container->getDefinition($id);

            if (!isset($tag[0]['service'])) {
                throw new RuntimeException(
                    sprintf("Tag '%s' for service '%s' doesn't have required param 'service'", self::TAG_NAME, $id)
                );
            }

            if (!$container->hasDefinition($tag[0]['service'])) {
                throw new RuntimeException(
                    sprintf(
                        "Target service '%s' is undefined. Link Service '%s' with tag '%s' and tag-service '%s' ",
                        $tag[0]['service'],
                        $id,
                        self::TAG_NAME,
                        $tag[0]['service']
                    )
                );
            }

            $service->setClass('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceLink');
            $service->setArguments(
                array(
                    new Reference('service_container'),
                    $tag[0]['service']
                )
            );
        }
    }
}
