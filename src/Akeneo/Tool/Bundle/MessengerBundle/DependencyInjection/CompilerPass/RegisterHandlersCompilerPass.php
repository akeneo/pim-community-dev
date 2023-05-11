<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\DependencyInjection\CompilerPass;

use Akeneo\Tool\Bundle\MessengerBundle\Config\MessengerConfigLoader;
use Akeneo\Tool\Bundle\MessengerBundle\Registry\MessageHandlerRegistry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RegisterHandlersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $registryDefinition = $container->getDefinition(MessageHandlerRegistry::class);

        $projectDir = $container->getParameter('kernel.project_dir');
        $env = $container->getParameter('kernel.environment');
        $config = MessengerConfigLoader::loadConfig($projectDir, $env);
        if ([] === $config) {
            return;
        }

        foreach ($config['queues'] as $queueConfig) {
            foreach ($queueConfig['consumers'] as $consumerConfig) {
                $registryDefinition->addMethodCall('registerHandler', [
                    new Reference($consumerConfig['service_handler']),
                    $consumerConfig['name']
                ]);
            }
        }
    }
}
