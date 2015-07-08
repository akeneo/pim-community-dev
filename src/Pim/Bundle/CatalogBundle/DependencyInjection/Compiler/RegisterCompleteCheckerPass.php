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
class RegisterCompleteCheckerPass implements CompilerPassInterface
{
    /** @staticvar string */
    const REGISTRY = 'pim_catalog.completeness.checker.registry';

    /** @staticvar string */
    const SERVICE_TAG = 'completeness.checker.attribute';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::REGISTRY)) {
            return;
        }

        $service = $container->getDefinition(self::REGISTRY);

        $taggedServices = $container->findTaggedServiceIds(self::SERVICE_TAG);

        foreach (array_keys($taggedServices) as $id) {
            $service->addMethodCall('registerAttributeChecker', [new Reference($id)]);
        }
    }
}
