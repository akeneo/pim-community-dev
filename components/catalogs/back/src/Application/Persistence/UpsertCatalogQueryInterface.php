<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence;

use Akeneo\Catalogs\ServiceAPI\Model\Catalog;

interface UpsertCatalogQueryInterface
{
    public function execute(Catalog $catalog): void;
}
