<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Adds entity transformers to the entity transformer registry
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterEntityTransformersPass implements CompilerPassInterface
{
    /**
     * @staticvar string The registry service id
     */
    const TRANSFORMER_REGISTRY_SERVICE = 'pim_import_export.transformer.registry';

    /**
     * @staticvar string The tag for entity transformers
     */
    const TRANSFORMER_TAG = 'pim_import_export.transformer.entity';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(self::TRANSFORMER_REGISTRY_SERVICE);
        foreach ($container->findTaggedServiceIds(self::TRANSFORMER_TAG) as $serviceId => $tags) {
            $definition->addMethodCall('addEntityTransformer', array($tags[0]['entity'], $serviceId));
        }
    }
}
