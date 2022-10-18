<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductIdentifiersQueryInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductIdentifiersQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductIdentifiersHandler
{
    public function __construct(
        private GetProductIdentifiersQueryInterface $query,
        private GetCatalogQueryInterface $getCatalogQuery
    ) {
    }

    /**
     * @return array<string>
     * @throws ServiceApiCatalogNotFoundException
     */
    public function __invoke(GetProductIdentifiersQuery $query): array
    {
        try {
            $catalogDomain = $this->getCatalogQuery->execute($query->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new ServiceApiCatalogNotFoundException();
        }

        return $this->query->execute(
            $catalogDomain,
            $query->getSearchAfter(),
            $query->getLimit(),
        );
    }
}
