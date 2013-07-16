<?php

namespace Oro\Bundle\EntityBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\ExtendCompilerPass;

class OroEntityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        //$container->addCompilerPass(new ExtendCompilerPass());
    }
}
