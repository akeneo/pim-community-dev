<?php

namespace Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration;

use Akeneo\Platform\Syndication\Domain\Model\PlatformConfiguration;

interface FindPlatformConfigurationQueryInterface
{
    /**
     * @return PlatformConfiguration
     */
    public function execute(string $platformConfigurationCode): PlatformConfiguration;
}
