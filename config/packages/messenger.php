<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Akeneo\Tool\Bundle\MessengerBundle\Config\MessengerConfigBuilder;
use Akeneo\Tool\Component\Messenger\Config\TransportType;

return static function (ContainerConfigurator $containerConfigurator) {
    $transportType = match ($containerConfigurator->env()) {
        'behat', 'test' => TransportType::PUB_SUB,
        'test_fake' => TransportType::IN_MEMORY,
        default => TransportType::DOCTRINE,
    };

    $configBuilder = new MessengerConfigBuilder($containerConfigurator->env());
    $config = $configBuilder->build(__DIR__ . '/../..', $transportType);

    $containerConfigurator->extension('framework', ['messenger' => $config]);
};
