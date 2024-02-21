<?php

namespace Akeneo\Pim\Structure\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Register reference data configurations into the registry
 *
 * @author    Julien Janvier <jjanvier@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RegisterReferenceDataConfigurationsPass implements CompilerPassInterface
{
    const REFERENCE_DATA_REGISTRY = 'pim_reference_data.registry';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::REFERENCE_DATA_REGISTRY);
        $referenceData = $container->getParameter('pim_reference_data.configurations');

        if (is_array($referenceData)) {
            foreach ($referenceData as $name => $rawConfig) {
                $registry->addMethodCall('registerRaw', [$rawConfig, $name]);
            }
        }
    }
}
