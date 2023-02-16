<?php

declare(strict_types=1);

namespace Akeneo\Pim\Platform\Messaging\Infrastructure\Symfony\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Messenger\MessageBus;
use Symfony\Component\Messenger\Transport\Sync\SyncTransport;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AkeneoMessagingCompilerPass  implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        // See vendor/symfony/framework-bundle/DependencyInjection/FrameworkExtension.php !

        
        // Create transport
        $queue = 'launch_product_and_product_model_evaluations_queue';
        $this->createTransport($queue, ['dsn' => 'sync://'], $container);

        $consumerName = 'dqi_launch_product_and_product_model_evaluations_consumer';
        $handlerService = 'Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Handler\LaunchProductAndProductModelEvaluationsHandler';
        // Add first bus
        $busId = 'command.bus.' . $consumerName;
        $container->register($busId, MessageBus::class)->addArgument([])->addTag('messenger.bus');
        // Plug handler to the bus
        $handler1 = $container->getDefinition($handlerService);
        $handler1->addTag('messenger.message_handler', ['bus' => $busId]);

        $consumerName = 'second_consumer_for_tests';
        $handlerService = 'Akeneo\Pim\Automation\DataQualityInsights\Infrastructure\Handler\SecondHandler';
        // Add second bus
        $busId = 'command.bus.' . $consumerName;
        $container->register($busId, MessageBus::class)->addArgument([])->addTag('messenger.bus');
        // Plug handler to the second bus
        $handler2 = $container->getDefinition($handlerService);
        $handler2->addTag('messenger.message_handler', ['bus' => $busId]);
    }

    private function createTransport(string $name, array $transport, ContainerBuilder $container)
    {
        $serializerId = $transport['serializer'] ?? 'messenger.default_serializer';
        $transport['options'] = $transport['options'] ?? [];
        $transportDefinition = (new Definition(TransportInterface::class))
            ->setFactory([new Reference('messenger.transport_factory'), 'createTransport'])
            ->setArguments([$transport['dsn'], $transport['options'] + ['transport_name' => $name], new Reference($serializerId)])
            ->addTag('messenger.receiver', [
                    'alias' => $name,
                    'is_failure_transport' => false,
                ]
            )
        ;
        $container->setDefinition($transportId = 'messenger.transport.'.$name, $transportDefinition);
//        $senderAliases[$name] = $transportId;

        if (null !== $transport['retry_strategy']['service']) {
//            $transportRetryReferences[$name] = new Reference($transport['retry_strategy']['service']);
        } else {
            $retryServiceId = sprintf('messenger.retry.multiplier_retry_strategy.%s', $name);
            $retryDefinition = new ChildDefinition('messenger.retry.abstract_multiplier_retry_strategy');
            $retryDefinition
                ->replaceArgument(0, $transport['retry_strategy']['max_retries'])
                ->replaceArgument(1, $transport['retry_strategy']['delay'])
                ->replaceArgument(2, $transport['retry_strategy']['multiplier'])
                ->replaceArgument(3, $transport['retry_strategy']['max_delay']);
            $container->setDefinition($retryServiceId, $retryDefinition);

//            $transportRetryReferences[$name] = new Reference($retryServiceId);
        }
    }
}
