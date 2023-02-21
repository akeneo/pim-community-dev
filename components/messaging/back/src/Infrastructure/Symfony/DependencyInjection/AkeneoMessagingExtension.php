<?php

declare(strict_types=1);

namespace Akeneo\Pim\Platform\Messaging\Infrastructure\Symfony\DependencyInjection;

use Akeneo\Pim\Platform\Messaging\Domain\MessageTenantAwareInterface;
use Akeneo\Pim\Platform\Messaging\Infrastructure\MessageHandler;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Yaml\Yaml;

/**
 * @copyright 2023 Akeneo SAS (https://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class AkeneoMessagingExtension  extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        // @fixme: Find a better way to load the config file
        $messagingConfigs = Yaml::parse(file_get_contents(__DIR__ . '/../../../../../../../config/messaging.yml'));

        // Register a handler for each consumer of each queue
        foreach ($messagingConfigs['queues'] as $queueConfig) {
            foreach ($queueConfig['consumers'] as $consumerConfig) {
                $container->register('akeneo.messaging.handler.'.$consumerConfig['name'], MessageHandler::class)
                    ->setArguments([
                        new Reference('akeneo_batch_queue.messenger.serializer'),
                        $consumerConfig['name'],
                    ])
                    ->addTag('messenger.message_handler', [
                        'handles' => MessageTenantAwareInterface::class,
                        'from_transport' => $consumerConfig['name'],
                    ]);
            }
        }
    }
}
