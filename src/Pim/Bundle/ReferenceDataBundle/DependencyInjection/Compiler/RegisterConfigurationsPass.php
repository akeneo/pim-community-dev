<?php

namespace Pim\Bundle\ReferenceDataBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class RegisterConfigurationsPass implements CompilerPassInterface
{
    const REFERENCE_DATA_REGISTRY = 'pim_reference_data.registry';

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $registry = $container->getDefinition(self::REFERENCE_DATA_REGISTRY);
        $referenceData = $container->getParameter('pim_reference_data.configurations');

        foreach ($referenceData as $name => $rawConfig) {
            $registry->addMethodCall('registerRaw', [$rawConfig, $name]);
        }
    }
}
