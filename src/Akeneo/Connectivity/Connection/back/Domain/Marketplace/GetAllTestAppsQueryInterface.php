<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Marketplace;

use Akeneo\Connectivity\Connection\Domain\Marketplace\DTO\GetAllTestAppsResult;

interface GetAllTestAppsQueryInterface
{
    public function execute(): GetAllTestAppsResult;
}
