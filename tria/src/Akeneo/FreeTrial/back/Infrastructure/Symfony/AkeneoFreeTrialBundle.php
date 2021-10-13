<?php

declare(strict_types=1);

namespace Akeneo\FreeTrial\Infrastructure\Symfony;

use Akeneo\FreeTrial\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterInstallerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class AkeneoFreeTrialBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new RegisterInstallerPass());
    }
}
