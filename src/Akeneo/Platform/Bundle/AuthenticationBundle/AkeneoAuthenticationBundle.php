<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle;

use Akeneo\Platform\Bundle\AuthenticationBundle\DependencyInjection\Compiler\AddLoggerHandlerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AkeneoAuthenticationBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AddLoggerHandlerPass());
    }
}
