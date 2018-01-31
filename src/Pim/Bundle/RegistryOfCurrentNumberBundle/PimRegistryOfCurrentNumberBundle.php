<?php

namespace Pim\Bundle\RegistryOfCurrentNumberBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Pim\Bundle\PdfGeneratorBundle\DependencyInjection\Compiler;

class PimRegistryOfCurrentNumberBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new Compiler\RegisterRendererPass());
    }
}
