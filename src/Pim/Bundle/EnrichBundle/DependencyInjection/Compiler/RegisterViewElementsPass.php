<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Compiler pass to register tagged view elements in the view element registry
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterViewElementsPass implements CompilerPassInterface
{
    /** @staticvar string */
    const REGISTRY_ID = 'pim_enrich.view_element.registry';

    /** @staticvar string */
    const VIEW_ELEMENT_TAG = 'pim_enrich.view_element';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::REGISTRY_ID)) {
            return;
        }

        $definition = $container->getDefinition(static::REGISTRY_ID);

        foreach ($container->findTaggedServiceIds(static::VIEW_ELEMENT_TAG) as $serviceId => $tag) {
            if (!isset($tag[0]['type'])) {
                throw new \LogicException(sprintf('No type provided for the "%s" view element', $serviceId));
            }
            $type     = $tag[0]['type'];
            $position = isset($tag[0]['position']) ? $tag[0]['position'] : 0;
            $definition->addMethodCall('add', array(new Reference($serviceId), $type, $position));
        }
    }
}
