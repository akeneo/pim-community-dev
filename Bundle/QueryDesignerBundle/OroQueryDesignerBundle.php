<?php

namespace Oro\Bundle\QueryDesignerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\QueryDesignerBundle\DependencyInjection\Compiler\ConfigurationPass;
use Oro\Bundle\QueryDesignerBundle\DependencyInjection\Compiler\FilterTypesPass;

class OroQueryDesignerBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigurationPass());
        $container->addCompilerPass(new FilterTypesPass());
    }
}
