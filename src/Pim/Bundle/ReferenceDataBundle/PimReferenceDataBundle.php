<?php

namespace Pim\Bundle\ReferenceDataBundle;

use Pim\Bundle\ReferenceDataBundle\DependencyInjection\Compiler\RegisterConfigurationsPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PimReferenceDataBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterConfigurationsPass());
    }
}
