<?php

namespace Oro\Bundle\EntityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DoctrineSqlFiltersConfigurationPass;
use Oro\Bundle\EntityBundle\DependencyInjection\Compiler\DatagridConfigurationPass;

class OroEntityBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrineSqlFiltersConfigurationPass());
        $container->addCompilerPass(new DatagridConfigurationPass());
    }
}
