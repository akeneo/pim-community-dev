<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MessengerBundle\DependencyInjection;

use Akeneo\Tool\Bundle\MessengerBundle\Config\MessengerConfigBuilder;
use Akeneo\Tool\Bundle\MessengerBundle\Handler\MessageWrapperHandler;
use Akeneo\Tool\Bundle\MessengerBundle\Transport\MessengerProxy\MessageWrapper;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AkeneoMessengerExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('command.yml');
        $loader->load('normalizer.yml');
        $loader->load('purge.yml');
        $loader->load('registry.yml');
        $loader->load('transport.yml');

        $this->registerMessengerHandlers($container);
    }

    /**
     * For each consumer we register a new service to handle the messages based on the transport used.
     * The goal for the handler is to have the name of the consumer.
     */
    private function registerMessengerHandlers(ContainerBuilder $container): void
    {
        $projectDir = $container->getParameter('kernel.project_dir');
        $env = $container->getParameter('kernel.environment');
        $config = MessengerConfigBuilder::loadConfig($projectDir, $env);
        if ([] === $config) {
            return;
        }

        // Register a handler for each consumer of each queue
        foreach ($config['queues'] as $queueConfig) {
            foreach ($queueConfig['consumers'] as $consumerConfig) {
                $container->register(
                    'akeneo.messaging.handler.'.$consumerConfig['name'],
                    MessageWrapperHandler::class
                )
                    ->setArguments([
                        new Reference('akeneo_messenger.message.serializer'),
                        new Reference('logger'),
                        $consumerConfig['name'],
                    ])
                    ->addTag('messenger.message_handler', [
                        'handles' => MessageWrapper::class,
                        'from_transport' => $consumerConfig['name'],
                    ]);
            }
        }
    }
}
