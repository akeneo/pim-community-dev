<?php

namespace Oro\Bundle\SecurityBundle;

use Oro\Bundle\SecurityBundle\DependencyInjection\Compiler\AclAnnotationProviderPass;
use Oro\Bundle\SecurityBundle\DependencyInjection\Compiler\AclConfigurationPass;
use Oro\Bundle\SecurityBundle\DependencyInjection\Compiler\ServiceLinkPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroSecurityBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AclConfigurationPass());
        $container->addCompilerPass(new AclAnnotationProviderPass());
        $container->addCompilerPass(new ServiceLinkPass());
    }
}
