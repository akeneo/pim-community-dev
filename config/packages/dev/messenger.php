<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Akeneo\Pim\Platform\Messaging\Domain\Config\TransportType;
use Akeneo\Pim\Platform\Messaging\Infrastructure\Config\MessengerConfigBuilder;

return static function (ContainerConfigurator $containerConfigurator) {
    $configBuilder = new MessengerConfigBuilder($containerConfigurator->env());
    $config = $configBuilder->build(TransportType::DOCTRINE);

    $containerConfigurator->extension('framework', ['messenger' => $config]);
};
