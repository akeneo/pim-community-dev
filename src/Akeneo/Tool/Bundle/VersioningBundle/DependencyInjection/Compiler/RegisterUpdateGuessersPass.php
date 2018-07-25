<?php

namespace Akeneo\Tool\Bundle\VersioningBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Dependency injection
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterUpdateGuessersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_versioning.update_guesser.chained')) {
            return;
        }

        $service = $container->getDefinition('pim_versioning.update_guesser.chained');

        $taggedServices = $container->findTaggedServiceIds('pim_versioning.update_guesser');

        foreach (array_keys($taggedServices) as $id) {
            $service->addMethodCall('addUpdateGuesser', [new Reference($id)]);
        }
    }
}
