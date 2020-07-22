<?php

namespace Akeneo\SharedCatalog\Query;

use Akeneo\SharedCatalog\Model\SharedCatalog;

interface FindProductIdentifiersQueryInterface
{
    public function find(SharedCatalog $sharedCatalog, array $options = []): array;
}
