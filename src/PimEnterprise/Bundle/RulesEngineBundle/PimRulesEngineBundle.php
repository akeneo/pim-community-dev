<?php

namespace Pim\Bundle\RulesEngineBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Pim\Bundle\RulesEngineBundle\DependencyInjection\Compiler\RegisterRunnerPass;

class PimRulesEngineBundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new RegisterRunnerPass());
    }
}
