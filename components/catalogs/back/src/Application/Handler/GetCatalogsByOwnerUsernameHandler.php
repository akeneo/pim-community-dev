<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogsByOwnerUsernameQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Model\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetCatalogsByOwnerUsernameQuery;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetCatalogsByOwnerUsernameHandler
{
    public function __construct(
        private GetCatalogsByOwnerUsernameQueryInterface $storageQuery,
    ) {
    }

    /**
     * @return array<Catalog>
     */
    public function __invoke(GetCatalogsByOwnerUsernameQuery $query): array
    {
        $offset = ($query->getPage() - 1) * $query->getLimit();

        return $this->storageQuery->execute($query->getOwnerUsername(), $offset, $query->getLimit());
    }
}
