<?php

namespace Akeneo\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Push the batch handler into the symfony logger
 *
 */
class PushBatchLogHandlerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('monolog.logger.batch')) {
            return;
        }

        $container
            ->getDefinition('monolog.logger.batch')
            ->addMethodCall('pushHandler', array(new Reference('akeneo_batch.logger.batch_log_handler')));
    }
}
