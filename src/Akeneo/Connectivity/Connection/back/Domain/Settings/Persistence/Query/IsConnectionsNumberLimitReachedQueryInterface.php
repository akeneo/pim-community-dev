<?php

declare(strict_types=1);

namespace Akeneo\Connectivity\Connection\Domain\Settings\Persistence\Query;

interface IsConnectionsNumberLimitReachedQueryInterface
{
    public function execute(): bool;
}
