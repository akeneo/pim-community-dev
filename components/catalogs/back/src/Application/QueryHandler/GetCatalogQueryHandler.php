<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\QueryHandler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\Domain\Model\Catalog;
use Akeneo\Catalogs\Domain\Query\GetCatalogQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class GetCatalogQueryHandler
{
    public function __construct(
        private FindOneCatalogByIdQueryInterface $findOneCatalogByIdQuery,
    ) {
    }

    public function __invoke(GetCatalogQuery $query): Catalog
    {
        $catalog = $this->findOneCatalogByIdQuery->execute($query->getId());

        if (null === $catalog) {
            throw new CatalogNotFoundException($query->getId());
        }

        return $catalog;
    }
}
