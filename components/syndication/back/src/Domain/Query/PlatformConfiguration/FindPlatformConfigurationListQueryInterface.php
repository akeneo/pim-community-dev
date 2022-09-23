<?php

namespace Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration;

use Akeneo\Platform\Syndication\Domain\Model\PlatformConfiguration;

interface FindPlatformConfigurationListQueryInterface
{
    /**
     * @return PlatformConfiguration[]
     */
    public function execute(): array;
}
