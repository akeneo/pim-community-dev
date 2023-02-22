<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence;

use Akeneo\Connectivity\Connection\Domain\CustomApps\DTO\GetAllCustomAppsResult;

interface GetAllCustomAppsQueryInterface
{
    public function execute(): GetAllCustomAppsResult;
}
