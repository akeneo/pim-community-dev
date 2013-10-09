<?php

namespace Oro\Bundle\DataGridBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass\ConfigurationPass;

class OroDataGridBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigurationPass());
    }
}
