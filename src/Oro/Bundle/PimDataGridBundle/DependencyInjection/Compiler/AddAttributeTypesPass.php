<?php

namespace Oro\Bundle\PimDataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Add grid configuration for attribute types
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeTypesPass implements CompilerPassInterface
{
    /** @staticvar string */
    const REGISTRY_ID = 'pim_datagrid.datagrid.configuration.product.configuration_registry';

    /** @staticvar string */
    const PARAM_PREFIX = 'pim_datagrid.product.attribute_type.';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::REGISTRY_ID);
        if ($registry) {
            $allParameters = $container->getParameterBag()->all();
            $parameterKeys = array_filter(
                array_keys($allParameters),
                function ($paramKey) {
                    return strpos($paramKey, self::PARAM_PREFIX) === 0;
                }
            );
            $parameters = array_intersect_key($allParameters, array_flip($parameterKeys));
            $configurations = [];
            foreach ($parameters as $key => $configuration) {
                $configurations[str_replace(self::PARAM_PREFIX, '', $key)] = $configuration;
            }
            $registry->addMethodCall('setConfigurations', [$configurations]);
        }
    }
}
