<?php

namespace Oro\Bundle\EntityConfigBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

use Oro\Bundle\EntityConfigBundle\Exception\RuntimeException;

class ServiceMethodPass implements CompilerPassInterface
{
    const TAG_NAME = 'oro_service_method';

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
                        "Target service '%s' is undefined. Service Method '%s' with tag '%s' and tag-service '%s' ",
                        $tag[0]['service'],
                        $id,
                        self::TAG_NAME,
                        $tag[0]['service']
                    )
                );
            }

            $serviceDefinition = $container->getDefinition($tag[0]['service']);
            $class = $container->getParameterBag()->resolveValue($serviceDefinition->getClass());
            if (!method_exists($class, $tag[0]['method'])) {
                throw new RuntimeException(
                    sprintf(
                        'Method "%s" for target service "%s" is undefined.'
                        . ' Service Method "%s:%s" with tag "%s" and tag-service "%s" ',
                        $tag[0]['method'],
                        $tag[0]['service'],
                        $id,
                        $tag[0]['method'],
                        self::TAG_NAME,
                        $tag[0]['service']
                    )
                );
            }

            $service->setClass('Oro\Bundle\EntityConfigBundle\DependencyInjection\Utils\ServiceMethod');
            $service->setArguments(
                array(
                    new Reference($tag[0]['service']),
                    $tag[0]['method']
                )
            );
        }
    }
}
