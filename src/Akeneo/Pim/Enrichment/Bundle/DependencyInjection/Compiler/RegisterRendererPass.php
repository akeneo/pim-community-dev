<?php

namespace Akeneo\Pim\Enrichment\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
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
    /** @var string */
    const REGISTRY_ID = 'pim_pdf_generator.renderer.registry';
    /** @var string */
    const RENDERER_TAG = 'pim_pdf_generator.renderer';
    /** @var int */
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
                $registryDefinition->addMethodCall('addRenderer', [new Reference($serviceId)]);
            }
        }
    }

    /**
     * Get tagged renderer services ordered by priority
     *
     * @param ContainerBuilder $container
     *
     * @return array
     */
    protected function getServicesByPriority(ContainerBuilder $container)
    {
        $priorities = [];
        foreach ($container->findTaggedServiceIds(static::RENDERER_TAG) as $serviceId => $tags) {
            $priority = isset($tags[0]['priority']) ? $tags[0]['priority'] : self::DEFAULT_PRIORITY;
            if (!isset($priorities[$priority])) {
                $priorities[$priority] = [];
            }
            $priorities[$priority][] = $serviceId;
        }
        krsort($priorities);

        return $priorities;
    }
}
