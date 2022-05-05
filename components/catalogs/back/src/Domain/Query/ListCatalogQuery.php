<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Domain\Query;

use Akeneo\Catalogs\Domain\Model\Catalog;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @implements Query<array<Catalog>>
 */
class ListCatalogQuery implements Query
{
    public function __construct(
        private int $page,
    ) {
    }

    public function getPage(): int
    {
        return $this->page;
    }
}
