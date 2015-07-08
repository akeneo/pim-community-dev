<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register attributes complete checkers tagged services
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterAttributeCompleteCheckersPass implements CompilerPassInterface
{
    /** @staticvar string */
    const SERVICE_CHAINED = 'pim_catalog.completeness.checker.product_value';

    /** @staticvar string */
    const SERVICE_TAG = 'completeness.checker.attribute';

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
            $service->addMethodCall('addAttributeChecker', [new Reference($id)]);
        }
    }
}
