<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query;

interface IsCustomAppsNumberLimitReachedQueryInterface
{
    public function execute(): bool;
}
