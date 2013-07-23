<?php

namespace Pim\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * CompilerPass Connector
 */
class ConnectorCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('pim_batch.connectors')) {
            return;
        }

        $registryDefinition = $container->getDefinition('pim_batch.connectors');
        $taggedJobServices = $container->findTaggedServiceIds('pim_batch.job');

        foreach ($taggedJobServices as $jobId => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $connectorId = $attributes['connector'];
                if (!$container->hasDefinition($connectorId)) {
                    throw new InvalidArgumentException(
                        sprintf('The connector service definition "%s" does not exist.', $connectorId)
                    );
                }
                $registryDefinition->addMethodCall(
                    'addJobToConnector',
                    array($connectorId, new Reference($connectorId), $jobId, new Reference($jobId))
                );
            }
        }
    }
}
