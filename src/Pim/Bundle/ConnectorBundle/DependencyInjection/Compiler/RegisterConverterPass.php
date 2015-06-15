<?php

namespace Pim\Bundle\ConnectorBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Register converters
 *
 * @author    Olivier Soulet <olivier.soulet@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterConverterPass implements CompilerPassInterface
{
    /** @staticvar */
    const CONVERTER_REGISTRY = 'pim_connector.array_converter.flat.product.value_converter.registry';

    /** @staticvar */
    const CONVERTER_TAG = 'pim_connector.array_converter.flat.product.value_converter';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->registerConverters($container);
    }

    /**
     * @param ContainerBuilder $container
     */
    protected function registerConverters(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::CONVERTER_REGISTRY);
        $converters = $container->findTaggedServiceIds(self::CONVERTER_TAG);

        foreach (array_keys($converters) as $converterId) {
            $registry->addMethodCall('register', [new Reference($converterId)]);
        }
    }
}
