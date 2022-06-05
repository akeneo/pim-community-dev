<?php

declare(strict_types=1);

namespace Akeneo\Test\PHPUnitDoctrineTransactionBundle\DependencyInjection;

use Akeneo\Test\PHPUnitDoctrineTransactionBundle\Doctrine\ConnectionDecoratorFactory;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DoctrineDriverWithAutoTransactionCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $connections = $container->getParameter('doctrine.connections');
        if (!is_array($connections)) {
            throw new \LogicException('Parameter "doctrine.connections" is either missing of misconfigured.');
        }

        $connectionFactoryDefinition = new Definition(ConnectionDecoratorFactory::class);
        $connectionFactoryDefinition
            ->setDecoratedService('doctrine.dbal.connection_factory')
            ->addArgument(new Reference('test.doctrine.dbal.connection_factory.inner'));
        $container->setDefinition('test.doctrine.dbal.connection_factory', $connectionFactoryDefinition);
    }
}
