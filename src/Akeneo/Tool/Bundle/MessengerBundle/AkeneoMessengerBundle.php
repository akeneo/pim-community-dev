<?php

namespace Akeneo\Tool\Bundle\MessengerBundle;

use Akeneo\Tool\Bundle\MessengerBundle\DependencyInjection\CompilerPass\RegisterHandlersCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoMessengerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterHandlersCompilerPass());
    }
}
