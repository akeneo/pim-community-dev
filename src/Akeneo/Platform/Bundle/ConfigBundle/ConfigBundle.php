<?php

namespace Akeneo\Platform\Bundle\ConfigBundle;

use Akeneo\Platform\Bundle\ConfigBundle\DependencyInjection\Compiler\ConfigPass;
use Akeneo\Platform\Bundle\ConfigBundle\DependencyInjection\Compiler\SystemConfigurationPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class ConfigBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigPass());
        $container->addCompilerPass(new SystemConfigurationPass());
    }
}
