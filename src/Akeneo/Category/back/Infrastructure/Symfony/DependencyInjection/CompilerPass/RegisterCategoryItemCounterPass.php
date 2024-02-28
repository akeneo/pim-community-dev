<?php

namespace Akeneo\Category\Infrastructure\Symfony\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register category counter to a registry.
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterCategoryItemCounterPass implements CompilerPassInterface
{
    public const CATEGORY_TAG = 'pim_enrich.doctrine.counter.category_item';

    public const CATEGORY_REGISTRY = 'pim_enrich.doctrine.counter.category_registry';

    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::CATEGORY_REGISTRY)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::CATEGORY_REGISTRY);

        foreach ($container->findTaggedServiceIds(static::CATEGORY_TAG) as $serviceId => $attributes) {
            foreach ($attributes as $attribute) {
                $registryDefinition->addMethodCall(
                    'register',
                    [
                        new Reference($serviceId),
                        $attribute['type'],
                    ],
                );
            }
        }
    }
}
