<?php

namespace Oro\Bundle\ConfigBundle;

use Oro\Bundle\ConfigBundle\DependencyInjection\Compiler\ConfigPass;
use Oro\Bundle\ConfigBundle\DependencyInjection\Compiler\SystemConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroConfigBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigPass());
        $container->addCompilerPass(new SystemConfigurationPass());
    }
}
