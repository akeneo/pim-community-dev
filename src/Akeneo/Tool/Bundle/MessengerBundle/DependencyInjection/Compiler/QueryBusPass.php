<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class QueryBusPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has('akeneo_messenger.query_bus')) {
            return;
        }

        $taggedServices = $container->findTaggedServiceIds('akeneo_messenger.query_bus');

        foreach ($taggedServices as $id => $tags) {
            $container->findDefinition($id)->addMethodCall('setQueryBus', [new Reference('akeneo_messenger.query_bus')]);
        }
    }
}
