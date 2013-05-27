<?php
namespace Oro\Bundle\DataFlowBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * CompilerPass Connector
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2012 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
 *
 */
class ConnectorCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('oro_dataflow.connectors')) {
            return;
        }

        $registryDefinition = $container->getDefinition('oro_dataflow.connectors');
        $taggedJobServices = $container->findTaggedServiceIds('oro_dataflow_job');

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
