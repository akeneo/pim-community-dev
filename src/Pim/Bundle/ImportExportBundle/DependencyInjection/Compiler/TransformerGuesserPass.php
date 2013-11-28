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
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $priorities = array();
        foreach ($container->findTaggedServiceIds(self::TRANSFORMER_TAG) as $serviceId => $tags) {
            $priority = isset($tags[0]['priority']) ? $tags[0]['priority'] : 100;
            if (!isset($priorities[$priority])) {
                $priorities[$priority] = array();
            }
            $priorities[$priority][] = $serviceId;
        }

        $definition = $container->getDefinition(self::CHAINED_TRANSFORMER_SERVICE);
        krsort($priorities);
        foreach ($priorities as $serviceIds) {
            foreach ($serviceIds as $serviceId) {
                $definition->addMethodCall('addGuesser', array(new Reference($serviceId)));
            }
        }
    }
}
