<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence;

use Akeneo\Catalogs\Domain\Model\Catalog;

interface GetAllCatalogsQueryInterface
{
    /**
     * @return array<Catalog>
     */
    public function execute(): array;
}
