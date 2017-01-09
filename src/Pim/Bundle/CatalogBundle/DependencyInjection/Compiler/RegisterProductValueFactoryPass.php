<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register product value factories inside their registry
 *
 * @author    Julien Janvier <j.janvier@gmail.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterProductValueFactoryPass implements CompilerPassInterface
{
    /** @staticvar string The registry id */
    const REGISTRY_ID = 'pim_catalog.factory.product_value.registry';

    /** @staticvar string */
    const TAG = 'pim_catalog.factory.product_value';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::REGISTRY_ID);

        foreach ($container->findTaggedServiceIds(static::TAG) as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                $priority = isset($attribute['priority']) ? $attribute['priority'] : 0;

                $registryDefinition->addMethodCall(
                    'register',
                    [new Reference($serviceId), $priority]
                );
            }
        }
    }
}
