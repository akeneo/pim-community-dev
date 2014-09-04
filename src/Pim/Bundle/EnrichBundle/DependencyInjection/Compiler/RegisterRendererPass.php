<?php

namespace Pim\Bundle\EnrichBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registers all defined renderers to the PDF renderers registry
 *
 * @author    Charles Pourcel <charles.pourcel@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterRendererPass implements CompilerPassInterface
{
    const REGISTRY_ID      = 'pim_enrich.renderer.registry';
    const RENDERER_TAG     = 'pim_enrich.renderer';
    const DEFAULT_PRIORITY = 100;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registryId = static::REGISTRY_ID;

        if (!$container->hasDefinition($registryId)) {
            return;
        }

        $registryDefinition = $container->getDefinition($registryId);

        foreach ($this->getServicesByPriority($container) as $serviceIds) {
            foreach ($serviceIds as $serviceId) {
                $registryDefinition->addMethodCall('addRenderer', array(new Reference($serviceId)));
            }
        }
    }

    /**
     * Get tagged guesser services ordered by priority
     *
     * @param ContainerBuilder $container
     *
     * @return array
     */
    protected function getServicesByPriority(ContainerBuilder $container)
    {
        $priorities = array();
        foreach ($container->findTaggedServiceIds(static::RENDERER_TAG) as $serviceId => $tags) {
            $priority = isset($tags[0]['priority']) ? $tags[0]['priority'] : self::DEFAULT_PRIORITY;
            if (!isset($priorities[$priority])) {
                $priorities[$priority] = array();
            }
            $priorities[$priority][] = $serviceId;
        }
        krsort($priorities);

        return $priorities;
    }
}
