<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence;

interface DeleteCatalogQueryInterface
{
    public function execute(string $id): void;
}
