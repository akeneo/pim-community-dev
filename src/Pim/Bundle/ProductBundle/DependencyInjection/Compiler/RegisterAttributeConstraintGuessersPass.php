<?php

namespace Pim\Bundle\ProductBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Dependency injection
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterAttributeConstraintGuessersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('oro_flexibleentity.validator.attribute_constraint_guesser')) {
            return;
        }

        $service = $container->getDefinition('oro_flexibleentity.validator.attribute_constraint_guesser');

        $taggedServices = $container->findTaggedServiceIds('pim.attribute_constraint_guesser');

        foreach (array_keys($taggedServices) as $id) {
            $service->addMethodCall('addConstraintGuesser', array(new Reference($id)));
        }
    }
}
