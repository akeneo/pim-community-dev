<?php

declare(strict_types=1);

namespace Akeneo\Platform\Bundle\AuthenticationBundle;

use Akeneo\Platform\Bundle\AuthenticationBundle\DependencyInjection\Compiler\EnableSsoFirewallPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AkeneoAuthenticationBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EnableSsoFirewallPass());
    }
}
