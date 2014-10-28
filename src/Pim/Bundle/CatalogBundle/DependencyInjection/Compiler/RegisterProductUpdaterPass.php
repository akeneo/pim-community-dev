<?php

namespace Pim\Bundle\CatalogBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register product updaters
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterProductUpdaterPass implements CompilerPassInterface
{
    /** @staticvar */
    const SETTER_REGISTRY = 'pim_catalog.updater.setter.registry';

    /** @staticvar */
    const SETTER_TAG = 'pim_catalog.updater.setter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::SETTER_REGISTRY)) {
            return;
        }

        $registry = $container->getDefinition(self::SETTER_REGISTRY);
        $setters = $container->findTaggedServiceIds(self::SETTER_TAG);

        foreach (array_keys($setters) as $setterId) {
            $registry->addMethodCall('register', [new Reference($setterId)]);
        }

        // TODO : same for copier
    }
}
