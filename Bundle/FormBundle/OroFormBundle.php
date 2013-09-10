<?php

namespace Oro\Bundle\FormBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\FormBundle\DependencyInjection\Compiler\AutocompleteCompilerPass;
use Oro\Bundle\FormBundle\DependencyInjection\Compiler\FormCompilerPass;

class OroFormBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AutocompleteCompilerPass());
        $container->addCompilerPass(new FormCompilerPass());
    }
}
