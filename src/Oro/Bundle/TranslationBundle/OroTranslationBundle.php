<?php

namespace Oro\Bundle\TranslationBundle;

use Oro\Bundle\TranslationBundle\DependencyInjection\Compiler\OverrideCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class OroTranslationBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideCompilerPass());
    }
}
