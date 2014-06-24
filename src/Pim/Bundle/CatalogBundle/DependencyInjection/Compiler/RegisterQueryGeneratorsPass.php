<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Dependency injection
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterQueryGeneratorsPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_catalog.event_listener.mongodb.update_normalized_product_data')) {
            return;
        }

        $service = $container->getDefinition('pim_catalog.event_listener.mongodb.update_normalized_product_data');

        $taggedServices = $container->findTaggedServiceIds('pim_catalog.query_generator');

        foreach (array_keys($taggedServices) as $id) {
            $service->addMethodCall('addQueryGenerator', array(new Reference($id)));
        }
    }
}
