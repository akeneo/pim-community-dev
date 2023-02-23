<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\DependencyInjection\CompilerPass;

use Akeneo\Tool\Bundle\MessengerBundle\Command\ProcessMessageCommand;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class RegisterHandlersCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $processMessageCommandDefinition = $container->getDefinition(ProcessMessageCommand::class);

        $projectDir = $container->getParameter('kernel.project_dir');
        $messagingConfigs = Yaml::parse(file_get_contents($projectDir . '/config/messaging.yml'));

        foreach ($messagingConfigs['queues'] as $queueConfig) {
            foreach ($queueConfig['consumers'] as $consumerConfig) {
                $processMessageCommandDefinition->addMethodCall('registerHandler', [
                    new Reference($consumerConfig['service_handler']),
                    $consumerConfig['name']
                ]);
            }
        }
    }
}
