<?php

namespace Oro\Bundle\LocaleBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\LocaleBundle\DependencyInjection\Compiler\AddDateTimeFormatConverterCompilerPass;
use Oro\Bundle\LocaleBundle\DependencyInjection\Compiler\TwigSandboxConfigurationPass;

class OroLocaleBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddDateTimeFormatConverterCompilerPass());
        $container->addCompilerPass(new TwigSandboxConfigurationPass());
    }
}
