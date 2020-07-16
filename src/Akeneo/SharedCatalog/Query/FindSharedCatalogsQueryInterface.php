<?php

namespace Akeneo\SharedCatalog\Query;

use Akeneo\SharedCatalog\Model\SharedCatalog;

interface FindSharedCatalogsQueryInterface
{
    /**
     * @return SharedCatalog[]
     */
    public function execute(): array;
}
