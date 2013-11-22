<?php

namespace Pim\Bundle\CustomEntityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class PimCustomEntityBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new DependencyInjection\Compiler\ConfigurationRegistryPass)
            ->addCompilerPass(new DependencyInjection\Compiler\DatagridManagerPass);
    }
}
