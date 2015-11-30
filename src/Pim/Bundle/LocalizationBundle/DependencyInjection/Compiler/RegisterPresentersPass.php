<?php

namespace Pim\Bundle\LocalizationBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass that register presenters
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterPresentersPass implements CompilerPassInterface
{
    const LOCALIZATION_PRESENTER_REGISTRY = 'pim_localization.presenter.registry';

    const LOCALIZATION_PRESENTER          = 'pim_localization.presenter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::LOCALIZATION_PRESENTER_REGISTRY)) {
            return;
        }

        $definition = $container->getDefinition(self::LOCALIZATION_PRESENTER_REGISTRY);

        foreach ($container->findTaggedServiceIds(self::LOCALIZATION_PRESENTER) as $id => $presenter) {
            $definition->addMethodCall(
                'addPresenter', [ new Reference($id) ]
            );
        }
    }
}
