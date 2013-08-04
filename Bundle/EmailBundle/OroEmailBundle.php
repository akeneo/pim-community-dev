<?php

namespace Oro\Bundle\EmailBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Oro\Bundle\EmailBundle\DependencyInjection\Compiler\EmailAddressConfigurationPass;

class OroEmailBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new EmailAddressConfigurationPass());
    }
}
