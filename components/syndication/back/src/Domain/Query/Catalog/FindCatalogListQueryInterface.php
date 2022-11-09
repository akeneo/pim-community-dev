<?php

namespace Akeneo\Platform\Syndication\Domain\Query\Catalog;

use Akeneo\Platform\Syndication\Domain\Model\Catalog;

interface FindCatalogListQueryInterface
{
    /**
     * @return Catalog[]
     */
    public function execute(): array;
}
