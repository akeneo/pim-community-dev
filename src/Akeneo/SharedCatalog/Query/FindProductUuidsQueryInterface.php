<?php

namespace Akeneo\SharedCatalog\Query;

use Akeneo\SharedCatalog\Model\SharedCatalog;

interface FindProductUuidsQueryInterface
{
    /**
     * @param array<string, mixed> $options:
     *  {
            "search_after": null|string (uuid)
     *  }
     * @return string[]
     */
    public function find(SharedCatalog $sharedCatalog, array $options = []): array;
}
