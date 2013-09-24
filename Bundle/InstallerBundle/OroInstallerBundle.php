<?php

namespace Oro\Bundle\InstallerBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\InstallerBundle\DependencyInjection\Compiler\InstallerPass;

class OroInstallerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new InstallerPass());
    }
}
