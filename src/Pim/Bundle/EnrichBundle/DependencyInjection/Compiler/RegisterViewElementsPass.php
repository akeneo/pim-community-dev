<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use Pim\Bundle\EnrichBundle\DependencyInjection\Reference\ReferenceFactory;
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
    /** @staticvar int The default view element position */
    const DEFAULT_POSITION = 100;

    /** @staticvar string The registry id */
    const REGISTRY_ID = 'pim_enrich.view_element.registry';

    /** @staticvar string */
    const VIEW_ELEMENT_TAG = 'pim_enrich.view_element';

    /** @var ReferenceFactory */
    protected $factory;

    /**
     * @param ReferenceFactory $factory
     */
    public function __construct(ReferenceFactory $factory = null)
    {
        $this->factory = $factory ?: new ReferenceFactory();
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::REGISTRY_ID)) {
            return;
        }

        $definition = $container->getDefinition(static::REGISTRY_ID);

        foreach ($container->findTaggedServiceIds(static::VIEW_ELEMENT_TAG) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['type'])) {
                    throw new \LogicException(sprintf('No type provided for the "%s" view element', $serviceId));
                }
                $position = isset($tag['position']) ? $tag['position'] : static::DEFAULT_POSITION;
                $definition->addMethodCall(
                    'add',
                    [
                        $this->factory->createReference($serviceId),
                        $tag['type'],
                        $position
                    ]
                );
            }
        }
    }
}
