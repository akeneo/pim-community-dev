<?php

namespace Akeneo\Tool\Bundle\ConnectorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Dependency injection
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterArchiversPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_connector.event_listener.archivist')) {
            return;
        }

        $service = $container->getDefinition('pim_connector.event_listener.archivist');

        $taggedServices = $container->findTaggedServiceIds('pim_connector.archiver');

        foreach (array_keys($taggedServices) as $id) {
            $service->addMethodCall('registerArchiver', [new Reference($id)]);
        }
    }
}
