<?php

namespace Oro\Bundle\ConfigBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\ConfigBundle\DependencyInjection\Compiler\ConfigPass;
use Oro\Bundle\ConfigBundle\DependencyInjection\Compiler\SystemConfigurationPass;

class OroConfigBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigPass());
        $container->addCompilerPass(new SystemConfigurationPass());
    }
}
