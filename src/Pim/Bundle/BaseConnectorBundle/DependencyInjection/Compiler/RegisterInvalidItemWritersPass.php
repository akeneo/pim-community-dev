<?php

namespace Pim\Bundle\BaseConnectorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author    Adrien Pétremann <adrien.petremann@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class RegisterInvalidItemWritersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_base_connector.event_listener.invalid_items_writer_registry')) {
            return;
        }

        $service = $container->getDefinition('pim_base_connector.event_listener.invalid_items_writer_registry');

        $taggedServices = $container->findTaggedServiceIds('pim_base_connector.invalid_items_writer');

        foreach (array_keys($taggedServices) as $id) {
            $service->addMethodCall('registerWriter', [new Reference($id)]);
        }
    }
}
