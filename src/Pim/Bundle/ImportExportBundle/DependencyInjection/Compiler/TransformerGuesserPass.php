<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds guessers to the chain guessser
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformerGuesserPass implements CompilerPassInterface
{
    /**
     * @staticvar string The id of the product reader service
     */
    const CHAINED_TRANSFORMER_SERVICE = 'pim_import_export.transformer.guesser';

    /**
     * @staticvar string The tag for FixtureBuilder services
     */
    const TRANSFORMER_TAG = 'pim_import_export.transformer.guesser';

    /**
     * @staticvar default priority applied on guesser
     */
    const DEFAULT_PRIORITY = 100;

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $priorities = $this->getServicesByPriority($container);
        $definition = $container->getDefinition(self::CHAINED_TRANSFORMER_SERVICE);
        foreach ($priorities as $serviceIds) {
            foreach ($serviceIds as $serviceId) {
                $definition->addMethodCall('addGuesser', [new Reference($serviceId)]);
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
        $priorities = [];
        foreach ($container->findTaggedServiceIds(self::TRANSFORMER_TAG) as $serviceId => $tags) {
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
