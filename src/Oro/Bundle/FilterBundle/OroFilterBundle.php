<?php

namespace Oro\Bundle\FilterBundle;

use Oro\Bundle\FilterBundle\DependencyInjection\CompilerPass\FilterTypesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroFilterBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new FilterTypesPass());
    }
}
