<?php

declare(strict_types=1);

namespace Akeneo\Test\PHPUnitDoctrineTransactionBundle;

use Akeneo\Test\PHPUnitDoctrineTransactionBundle\DependencyInjection\DoctrineDriverWithAutoTransactionCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class PHPUnitDoctrineTransactionBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new DoctrineDriverWithAutoTransactionCompilerPass());
    }
}
