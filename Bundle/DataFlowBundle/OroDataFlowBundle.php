<?php

namespace Oro\Bundle\DataFlowBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Oro\Bundle\DataFlowBundle\DependencyInjection\Compiler\ConnectorCompilerPass;

/**
 * DataFlow bundle
 *
 *
 */
class OroDataFlowBundle extends Bundle
{

    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new ConnectorCompilerPass());
    }
}
