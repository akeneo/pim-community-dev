<?php

namespace Pim\Bundle\LocalizationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that register localizers
 *
 * @author    Marie Bochu <marie.bochu@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterLocalizersPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_localization.localizer.registry')) {
            return;
        }

        $definition = $container->getDefinition('pim_localization.localizer.registry');
        foreach ($container->findTaggedServiceIds('pim_localization.localizer') as $id => $localizer) {
            $definition->addMethodCall(
                'addLocalizer',
                [
                    new Reference($id)
                ]
            );
        }
    }
}
