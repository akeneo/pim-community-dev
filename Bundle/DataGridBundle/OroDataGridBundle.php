<?php

namespace Oro\Bundle\DataGridBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass\ActionsPass;
use Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass\MassActionsPass;
use Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass\FormattersPass;
use Oro\Bundle\DataGridBundle\DependencyInjection\CompilerPass\ConfigurationPass;

class OroDataGridBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigurationPass());
        $container->addCompilerPass(new FormattersPass());
        $container->addCompilerPass(new ActionsPass());
        $container->addCompilerPass(new MassActionsPass());
    }
}
