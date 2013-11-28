<?php

namespace Oro\Bundle\GridBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\GridBundle\DependencyInjection\Compiler\AddDependencyCallsCompilerPass;
use Oro\Bundle\GridBundle\DependencyInjection\Compiler\AddFilterTypeCompilerPass;

class OroGridBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddDependencyCallsCompilerPass());
        $container->addCompilerPass(new AddFilterTypeCompilerPass());
    }
}
