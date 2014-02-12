<?php

namespace Akeneo\Bundle\BatchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Akeneo\Bundle\BatchBundle\DependencyInjection\Compiler;

/**
 * Batch Bundle
 *
 */
class AkeneoBatchBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new Compiler\RegisterNotifiersPass())
            ->addCompilerPass(new Compiler\PushBatchLogHandlerPass())
            ->addCompilerPass(new Compiler\RegisterJobsPass());
    }
}
