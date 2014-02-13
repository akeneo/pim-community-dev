<?php

namespace Akeneo\Bundle\BatchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Akeneo\Bundle\BatchBundle\DependencyInjection\Compiler;

/**
 * Batch Bundle
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/MIT MIT
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
