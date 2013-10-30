<?php

namespace Pim\Bundle\ImportExportBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Adds property transformers to the product transformer
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class PropertyTransformersPass implements CompilerPassInterface
{
    const PRODUCT_TRANSFORMER_SERVICE = 'pim_import_export.transformer.product';
    const PRODUCT_PROPERTY_TAG = 'pim_import_export.transformer.product.property';
    const PRODUCT_ATTRIBUTE_TAG = 'pim_import_export.transformer.product.attribute';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $definition = $container->getDefinition(self::PRODUCT_TRANSFORMER_SERVICE);

        foreach ($container->findTaggedServiceIds(self::PRODUCT_PROPERTY_TAG) as $id => $tags) {
            foreach ($tags as $tag) {
                $options = $tag;
                unset($options['property_path']);
                $definition->addMethodCall(
                    'addPropertyTransformer',
                    array(
                        $tag['property_path'],
                        new Reference($id),
                        $options
                    )
                );
            }
        }

        foreach ($container->findTaggedServiceIds(self::PRODUCT_ATTRIBUTE_TAG) as $id => $tags) {
            foreach ($tags as $tag) {
                $options = $tag;
                unset($options['backend_type']);
                $definition->addMethodCall(
                    'addAttributeTransformer',
                    array(
                        $tag['backend_type'],
                        new Reference($id),
                        $options
                    )
                );
            }
        }
    }
}
