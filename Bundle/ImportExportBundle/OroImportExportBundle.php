<?php

namespace Oro\Bundle\ImportExportBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Oro\Bundle\ImportExportBundle\DependencyInjection\Compiler\AddConverterCompilerPass;

class OroImportExportBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new AddConverterCompilerPass());
    }
}
