<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Akeneo\Pim\Platform\Messaging\Domain\Config\MessengerConfigBuilder;
use Akeneo\Pim\Platform\Messaging\Domain\Config\TransportType;
use Symfony\Component\Yaml\Yaml;

return static function (ContainerConfigurator $containerConfigurator) {
    $messagingConfigs = Yaml::parse(file_get_contents(__DIR__ . '/../../messaging.yml'));

    $configBuilder = new MessengerConfigBuilder($containerConfigurator->env());
    $config = $configBuilder->build($messagingConfigs, TransportType::DOCTRINE);

    $containerConfigurator->extension('framework', ['messenger' => $config]);
};
