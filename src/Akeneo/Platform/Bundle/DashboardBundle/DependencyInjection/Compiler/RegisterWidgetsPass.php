<?php

namespace Akeneo\Platform\Bundle\DashboardBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register widget into registry compiler pass
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterWidgetsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_dashboard.widget.registry')) {
            return;
        }

        $definition = $container->getDefinition('pim_dashboard.widget.registry');
        foreach ($container->findTaggedServiceIds('pim_dashboard.widget') as $serviceId => $tag) {
            $position = isset($tag[0]['position']) ? $tag[0]['position'] : 0;
            $definition->addMethodCall('add', [new Reference($serviceId), $position]);
        }
    }
}
