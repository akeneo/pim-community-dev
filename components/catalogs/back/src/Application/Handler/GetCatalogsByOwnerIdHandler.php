<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\GetCatalogsByOwnerIdQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogsByOwnerIdQuery;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogsByOwnerIdHandler
{
    public function __construct(
        private GetCatalogsByOwnerIdQueryInterface $storageQuery,
    ) {
    }

    /**
     * @return array<Catalog>
     */
    public function __invoke(GetCatalogsByOwnerIdQuery $query): array
    {
        $offset = ($query->getPage() - 1) * $query->getLimit();

        return $this->storageQuery->execute($query->getOwnerId(), $offset, $query->getLimit());
    }
}
