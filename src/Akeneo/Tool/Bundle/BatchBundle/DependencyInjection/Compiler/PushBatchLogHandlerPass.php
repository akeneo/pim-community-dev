<?php

namespace Akeneo\Tool\Bundle\BatchBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Push the batch handler into the symfony logger
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
            ->addMethodCall('pushHandler', [new Reference('akeneo_batch.logger.batch_log_handler')]);
    }
}
