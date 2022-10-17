<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductUuidsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsHandler
{
    public function __construct(
        private GetProductUuidsQueryInterface $query,
        private GetCatalogQueryInterface $getCatalogQuery,
    ) {
    }

    /**
     * @return array<string>
     *
     * @throws ServiceApiCatalogNotFoundException
     */
    public function __invoke(GetProductUuidsQuery $query): array
    {
        try {
            $catalog = $this->getCatalogQuery->execute($query->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new ServiceApiCatalogNotFoundException();
        }

        return $this->query->execute(
            $catalog,
            $query->getSearchAfter(),
            $query->getLimit(),
            $query->getUpdatedAfter(),
            $query->getUpdatedBefore(),
        );
    }
}
