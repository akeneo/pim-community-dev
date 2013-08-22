<?php

namespace Pim\Bundle\BatchBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\BatchBundle\DependencyInjection\Compiler;

/**
 * Batch Bundle
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class PimBatchBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new Compiler\RegisterNotifiersPass());
        $container->addCompilerPass(new Compiler\PushBatchLogHandlerPass());
    }
}
