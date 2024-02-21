<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler\Localization;

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
    const LOCALIZATION_PRESENTER_REGISTRY = 'pim_catalog.localization.presenter.registry';

    const LOCALIZATION_PRESENTER_TAG = 'pim_catalog.localization.presenter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(self::LOCALIZATION_PRESENTER_REGISTRY)) {
            return;
        }
        $definition = $container->getDefinition(self::LOCALIZATION_PRESENTER_REGISTRY);

        foreach ($container->findTaggedServiceIds(self::LOCALIZATION_PRESENTER_TAG) as $id => $tags) {
            foreach ($tags as $tag) {
                $definition->addMethodCall('register', [new Reference($id), $tag['type']]);
            }
        }
    }
}
