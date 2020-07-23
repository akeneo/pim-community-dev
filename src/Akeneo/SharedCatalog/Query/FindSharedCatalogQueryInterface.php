<?php

namespace Akeneo\SharedCatalog\Query;

use Akeneo\SharedCatalog\Model\SharedCatalog;

interface FindSharedCatalogQueryInterface
{
    public function find(string $code): ?SharedCatalog;
}
