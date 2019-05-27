<?php

namespace Akeneo\Platform\Bundle\AuthenticationBundle\DependencyInjection\Compiler;

use Akeneo\Platform\Bundle\AuthenticationBundle\Sso\Log\MonologHandler;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

class AddLoggerHandlerPass implements CompilerPassInterface
{
    private const AUTHENTICATION_MONOLOG = 'monolog.handler.authentication';
    private const LOGS_STORAGE = 'oneup_flysystem.logs_storage_filesystem';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(self::AUTHENTICATION_MONOLOG)) {
            return;
        }

        $definition = $container->findDefinition(self::AUTHENTICATION_MONOLOG);
        $arguments = $definition->getArguments();
        array_unshift($arguments, new Reference(self::LOGS_STORAGE));

        $definition->setClass(MonologHandler::class);
        foreach ($arguments as $index => $argument) {
            $definition->setArgument($index, $argument);
        }
    }
}
