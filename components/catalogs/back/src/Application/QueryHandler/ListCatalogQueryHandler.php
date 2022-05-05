<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\QueryHandler;

use Akeneo\Catalogs\Application\Persistence\GetAllCatalogsQueryInterface;
use Akeneo\Catalogs\Domain\Model\Catalog;
use Akeneo\Catalogs\Domain\Query\ListCatalogQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ListCatalogQueryHandler
{
    public function __construct(
        private GetAllCatalogsQueryInterface $getAllCatalogsQuery,
    ) {
    }

    /**
     * @return array<Catalog>
     */
    public function __invoke(ListCatalogQuery $query): array
    {
        return $this->getAllCatalogsQuery->execute();
    }
}
