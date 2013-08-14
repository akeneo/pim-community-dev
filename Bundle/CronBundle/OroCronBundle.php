<?php

namespace Oro\Bundle\ConfigBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\ConfigBundle\DependencyInjection\Compiler\ConfigPass;

class OroConfigBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConfigPass());
    }
}
