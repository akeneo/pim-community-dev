<?php

namespace Pim\Bundle\Batch2Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * 
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class InjectEventDispatcherPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('event_dispatcher')) {
            return;
        }

        foreach ($container->getDefinitions() as $definition) {
            try {
                $reflClass = new \ReflectionClass($definition->getClass());
            } catch (\ReflectionException $e) {
                continue;
            }
            if ($reflClass->isSubclassOf('Pim\\Bundle\\Batch2Bundle\\EventDispatching\\DispatchingService')) {
                $definition->addMethodCall('setEventDispatcher', array(new Reference('event_dispatcher')));
            }
        }
    }
}
