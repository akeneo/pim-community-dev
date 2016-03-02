<?php

namespace Pim\Bundle\LocalizationBundle;

use Pim\Bundle\LocalizationBundle\DependencyInjection\Compiler\RegisterLocalizersPass;
use Pim\Bundle\LocalizationBundle\DependencyInjection\Compiler\RegisterPresentersPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PimLocalizationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterLocalizersPass());
        $container->addCompilerPass(new RegisterPresentersPass());
    }
}
