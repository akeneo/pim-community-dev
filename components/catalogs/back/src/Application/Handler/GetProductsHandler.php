<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductsWithFilteredValuesQueryInterface;
use Akeneo\Catalogs\Application\Service\DispatchInvalidCatalogDisabledEventInterface;
use Akeneo\Catalogs\Application\Validation\IsCatalogValidInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Product from GetProductsWithFilteredValuesQueryInterface
 */
final class GetProductsHandler
{
    public function __construct(
        private GetProductsWithFilteredValuesQueryInterface $getProductsQuery,
        private GetCatalogQueryInterface $getCatalogQuery,
        private DisableCatalogQueryInterface $disableCatalogQuery,
        private IsCatalogValidInterface $isCatalogValid,
        private DispatchInvalidCatalogDisabledEventInterface $dispatchInvalidCatalogDisabledEvent,
    ) {
    }

    /**
     * @return array<Product>
     *
     * @throws ServiceApiCatalogNotFoundException
     * @throws CatalogDisabledException
     */
    public function __invoke(GetProductsQuery $query): array
    {
        try {
            $catalogDomain = $this->getCatalogQuery->execute($query->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new ServiceApiCatalogNotFoundException();
        }

        if (!$catalogDomain->isEnabled()) {
            throw new CatalogDisabledException();
        }

        try {
            return $this->getProductsQuery->execute(
                $catalogDomain,
                $query->getSearchAfter(),
                $query->getLimit(),
                $query->getUpdatedAfter(),
                $query->getUpdatedBefore(),
            );
        } catch (\Exception $exception) {
            if (!($this->isCatalogValid)($catalogDomain)) {
                $this->disableCatalogQuery->execute($catalogDomain->getId());
                ($this->dispatchInvalidCatalogDisabledEvent)($catalogDomain->getId());
                throw new CatalogDisabledException(previous: $exception);
            }

            throw $exception;
        }
    }
}
