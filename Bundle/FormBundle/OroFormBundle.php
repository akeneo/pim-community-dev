<?php

namespace Oro\Bundle\FormBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\FormBundle\DependencyInjection\Compiler\AddAutocompleteSearchFactororiesCompilerPass;

class OroFormBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddAutocompleteSearchFactororiesCompilerPass());
    }
}
