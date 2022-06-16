<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence;

interface IsCatalogsNumberLimitReachedQueryInterface
{
    public function execute(int $ownerId): bool;
}
