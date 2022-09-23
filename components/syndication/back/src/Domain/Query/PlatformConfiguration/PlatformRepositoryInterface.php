<?php

declare(strict_types=1);

namespace Akeneo\Platform\Syndication\Domain\Query\PlatformConfiguration;

use Akeneo\Platform\Syndication\Domain\Model\Platform;
use Akeneo\Platform\Syndication\Domain\Model\PlatformFamily;

interface PlatformRepositoryInterface
{
    public function save(Platform $platform);
    public function saveFamily(PlatformFamily $platformFamily);
}
