<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Component\Yaml\Yaml;

function createTransportConfig($queueName): array {
    return [
        'dsn' => 'doctrine://default',
        'options' => [
            'table_name' => 'messenger_messages',
            'queue_name' => $queueName,
            'redeliver_timeout' => 86400,
            'auto_setup' => false,
        ],
        'serializer' => 'akeneo_batch_queue.messenger.serializer',
    ];
}

return static function (ContainerConfigurator $containerConfigurator) {
    $messagingConfigs = Yaml::parse(file_get_contents(__DIR__ . '/../../messaging.yml'));

    $transports = [];
    $routing = [];

    foreach ($messagingConfigs['queues'] as $name => $pimMessageConfig) {
        $transportName = $name;
        $transports[$transportName] = createTransportConfig($name);
        $routing[$pimMessageConfig['messageClass']] = $transportName;
    }

    $config = [
        'transports' => $transports,
        'routing' => $routing,
    ];

    print_r($config);

    $containerConfigurator->extension('framework', ['messenger' => $config]);
};
