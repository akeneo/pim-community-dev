<?php


namespace Akeneo\Tool\Bundle\LoggingBundle;

use Akeneo\Tool\Bundle\LoggingBundle\DependencyInjection\Compiler\LoggingProxyGeneratorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @copyright 2021 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class LoggingBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new LoggingProxyGeneratorPass());
    }
}
