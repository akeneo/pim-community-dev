<?php

namespace Oro\Bundle\QueryDesignerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class FilterTypesPass implements CompilerPassInterface
{
    const MANAGER_SERVICE_ID = 'oro_querydesigner.querydesigner.manager';
    const TAG_NAME           = 'oro_filter.extension.orm_filter.filter';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $managerDef = $container->getDefinition(self::MANAGER_SERVICE_ID);
        if ($managerDef) {
            $filters = $container->findTaggedServiceIds(self::TAG_NAME);
            foreach ($filters as $serviceId => $tags) {
                $tagAttrs = reset($tags);
                $managerDef->addMethodCall('addFilter', array($tagAttrs['type'], new Reference($serviceId)));
            }
        }
    }
}
