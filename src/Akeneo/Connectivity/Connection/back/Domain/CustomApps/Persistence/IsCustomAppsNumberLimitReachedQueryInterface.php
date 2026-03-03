<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\CustomApps\Persistence;

interface IsCustomAppsNumberLimitReachedQueryInterface
{
    public function execute(): bool;
}
