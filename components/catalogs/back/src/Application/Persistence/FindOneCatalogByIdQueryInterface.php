<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence;

use Akeneo\Catalogs\ServiceAPI\Model\Catalog;

interface FindOneCatalogByIdQueryInterface
{
    public function execute(string $id): ?Catalog;
}
