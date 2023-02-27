<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Akeneo\Tool\Bundle\MessengerBundle\Config\MessengerConfigBuilder;
use Akeneo\Tool\Component\Messenger\Config\TransportType;

return static function (ContainerConfigurator $containerConfigurator) {
    $configBuilder = new MessengerConfigBuilder($containerConfigurator->env());
    $config = $configBuilder->build(__DIR__ . '/../../..', TransportType::PUB_SUB);

    $containerConfigurator->extension('framework', ['messenger' => $config]);
};
