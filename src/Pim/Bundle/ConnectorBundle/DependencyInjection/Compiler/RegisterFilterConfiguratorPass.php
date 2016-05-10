<?php

namespace Pim\Bundle\ConnectorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register filter configurator
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterFilterConfiguratorPass implements CompilerPassInterface
{
    const REGISTRY = 'pim_connector.reader.doctrine.product_export_builder.filter_configurator_registry';
    const TAG = 'pim_connector.product_export_builder.filter_configurator';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerConverters($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function registerConverters(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::REGISTRY);
        $configurators = $container->findTaggedServiceIds(self::TAG);

        foreach (array_keys($configurators) as $configuratorId) {
            $registry->addMethodCall('register', [new Reference($configuratorId)]);
        }
    }
}
