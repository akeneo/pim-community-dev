<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use Pim\Bundle\EnrichBundle\DependencyInjection\Reference\ReferenceFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Compiler pass to register tagged view updaters in the view updater registry
 *
 * @author    Julien Sanchez <julien@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterViewUpdatersPass implements CompilerPassInterface
{
    /** @staticvar int The default view updater position */
    const DEFAULT_POSITION = 100;

    /** @staticvar string The registry id */
    const REGISTRY_ID = 'pim_enrich.form.view.view_updater.registry';

    /** @staticvar string */
    const VIEW_UPDATER_TAG = 'pim_enrich.form.view_updater';

    /** @var ReferenceFactory */
    protected $factory;

    /**
     * @param ReferenceFactory $factory
     */
    public function __construct(ReferenceFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(static::REGISTRY_ID)) {
            return;
        }

        $registryDefinition = $container->getDefinition(static::REGISTRY_ID);

        foreach ($container->findTaggedServiceIds(static::VIEW_UPDATER_TAG) as $serviceId => $tags) {
            foreach ($tags as $tag) {
                $this->registerViewUpdater($registryDefinition, $serviceId, $tag);
            }
        }
    }

    /**
     * Register a a view element to the view element registry
     *
     * @param Definition $registryDefinition
     * @param string     $serviceId
     * @param array      $tag
     */
    protected function registerViewUpdater(Definition $registryDefinition, $serviceId, $tag)
    {
        $position = isset($tag['position']) ? $tag['position'] : static::DEFAULT_POSITION;
        $registryDefinition->addMethodCall(
            'registerUpdater',
            [
                $this->factory->createReference($serviceId),
                $position
            ]
        );
    }
}
