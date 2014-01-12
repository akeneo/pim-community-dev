<?php

namespace Pim\Bundle\DataGridBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Add grid configuration for attribute types
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddAttributeTypesPass implements CompilerPassInterface
{
    /**
     * @var string
     */
    const REGISTRY_ID = 'pim_datagrid.datagrid.flexible.configuration_registry';

    /**
     * @var string
     */
    const PARAM_PREFIX = 'pim_datagrid.flexible.attribute_type.';

    /**
     * {@inheritDoc}
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
            $parameters     = array_intersect_key($allParameters, array_flip($parameterKeys));
            $configurations = array();
            foreach ($parameters as $key => $configuration) {
                $configurations[str_replace(self::PARAM_PREFIX, '', $key)]= $configuration;
            }

            // TODO process configuration to add default values

            $registry->addMethodCall('setConfigurations', array($configurations));
        }
    }
}
