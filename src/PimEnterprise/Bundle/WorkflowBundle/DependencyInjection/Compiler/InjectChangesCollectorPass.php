<?php

namespace PimEnterprise\Bundle\WorkflowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Inject the changes collector into services that are:
 *   - tagged with `pimee_workflow.collector`
 *   - implementing the ChangesCollectorAwareInterface
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class InjectChangesCollectorPass implements CompilerPassInterface
{
    /** @staticvar string */
    const COLLECTOR_AWARE_INTERFACE = 'PimEnterprise\Bundle\WorkflowBundle\Proposition\ChangesCollectorAwareInterface';

    /** @staticvar string */
    const COLLECTOR_SERVICE_ID = 'pimee_workflow.collector.changes';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::COLLECTOR_SERVICE_ID)) {
            return;
        }

        $collectorRef = new Reference(self::COLLECTOR_SERVICE_ID);
        foreach ($container->findTaggedServiceIds('pimee_workflow.collector') as $id => $attributes) {
            $serviceDef = $container->getDefinition($id);
            $refClass = new \ReflectionClass($serviceDef->getClass());
            if (!$refClass->implementsInterface(self::COLLECTOR_AWARE_INTERFACE)) {
                continue;
            }
            $serviceDef->addMethodCall('setCollector', [$collectorRef]);

        }
    }
}
