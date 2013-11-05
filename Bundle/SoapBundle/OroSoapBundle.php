<?php

namespace Oro\Bundle\SoapBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\SoapBundle\DependencyInjection\Compiler\LoadPass;

class OroSoapBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new LoadPass());
    }
}
