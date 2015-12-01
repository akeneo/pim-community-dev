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

    const LOCALIZATION_PRESENTER_TAG = 'pim_localization.presenter';

    const LOCALIZATION_PRESENTER_ATTRIBUTE_OPTION_TAG = 'pim_localization.presenter.attribute_option';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::LOCALIZATION_PRESENTER_REGISTRY)) {
            return;
        }

        $definition = $container->getDefinition(self::LOCALIZATION_PRESENTER_REGISTRY);

        $presenterTags = [
            self::LOCALIZATION_PRESENTER_TAG                  => 'addPresenter',
            self::LOCALIZATION_PRESENTER_ATTRIBUTE_OPTION_TAG => 'addAttributeOptionPresenter',
        ];

        foreach ($presenterTags as $tag => $methodName) {
            foreach ($container->findTaggedServiceIds($tag) as $id => $localizer) {
                $definition->addMethodCall($methodName, [new Reference($id)]);
            }
        }
    }
}
