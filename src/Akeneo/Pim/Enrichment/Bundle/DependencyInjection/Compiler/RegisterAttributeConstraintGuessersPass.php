<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

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
class RegisterAttributeConstraintGuessersPass implements CompilerPassInterface
{
    /** @staticvar string */
    const SERVICE_CHAINED = 'pim_catalog.validator.constraint_guesser.chained_attribute';

    /** @staticvar string */
    const SERVICE_TAG = 'pim_catalog.constraint_guesser.attribute';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SERVICE_CHAINED)) {
            return;
        }

        $service = $container->getDefinition(self::SERVICE_CHAINED);

        $taggedServices = $container->findTaggedServiceIds(self::SERVICE_TAG);

        foreach (array_keys($taggedServices) as $id) {
            $service->addMethodCall('addConstraintGuesser', [new Reference($id)]);
        }
    }
}
