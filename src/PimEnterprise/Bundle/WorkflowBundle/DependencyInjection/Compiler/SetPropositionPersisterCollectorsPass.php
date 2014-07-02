<?php

namespace PimEnterprise\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Set the proposition persister collectors
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class SetPropositionPersisterCollectorsPass implements CompilerPassInterface
{
    /** @staticvar string */
    const COLLECTOR_INTERFACE = 'PimEnterprise\Bundle\WorkflowBundle\Persistence\ProductValueChangesCollectorInterface';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_catalog.persister.product')) {
            return;
        }

        $collectors = [];
        foreach ($container->findTaggedServiceIds('pimee_workflow.collector') as $id => $attributes) {
            $collectorDef = $container->getDefinition($id);
            $refClass = new \ReflectionClass($collectorDef->getClass());
            if (!$refClass->implementsInterface(self::COLLECTOR_INTERFACE)) {
                throw new \RuntimeException(
                    sprintf(
                        'Class "%s" is tagged as a collector but it does not implement %s',
                        $collectorDef->getClass(),
                        self::COLLECTOR_INTERFACE
                    )
                );
            }

            $priority = isset($attributes[0]['priority']) ? $attributes[0]['priority'] : 0;
            $collectors[$priority][] = new Reference($id);
        }

        krsort($collectors);
        $sortedCollectors = [];
        foreach ($collectors as $key => $value) {
            $sortedCollectors = array_merge($sortedCollectors, $value);
            unset($sortedCollectors[$key]);
        }

        $definition = $container->getDefinition('pim_catalog.persister.product');
        $definition->addMethodCall('setCollectors', $collectors);
    }
}
