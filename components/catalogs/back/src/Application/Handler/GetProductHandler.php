<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\IsProductBelongingToCatalogQueryInterface;
use Akeneo\Catalogs\Application\Service\DispatchInvalidCatalogDisabledEventInterface;
use Akeneo\Catalogs\Application\Validation\IsCatalogValidInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogNotFoundException as ServiceApiCatalogNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Exception\ProductNotFoundException;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Product from GetProductQueryInterface
 */
final class GetProductHandler
{
    public function __construct(
        private readonly GetCatalogQueryInterface $getCatalogQuery,
        private readonly IsProductBelongingToCatalogQueryInterface $isProductBelongingToCatalogQuery,
        private readonly GetProductQueryInterface $getProductQuery,
        private readonly DisableCatalogQueryInterface $disableCatalogQuery,
        private readonly IsCatalogValidInterface $isCatalogValid,
        private readonly DispatchInvalidCatalogDisabledEventInterface $dispatchInvalidCatalogDisabledEvent,
    ) {
    }

    /**
     * @throws ServiceApiCatalogNotFoundException
     * @throws ProductNotFoundException
     * @throws CatalogDisabledException
     * @return array{uuid: string, enabled: bool, family: string, categories: string[], groups: string[], parent: string|null, values: array<string, array<string, mixed>>, associations: array<string, array{groups: string[], products: string[], product_models: string[]}>, quantified_associations: array<string, array{products: string[], product_models: string[]}>, created: string, updated: string}
     */
    public function __invoke(GetProductQuery $query): array
    {
        try {
            $catalog = $this->getCatalogQuery->execute($query->getCatalogId());
        } catch (CatalogNotFoundException) {
            throw new ServiceApiCatalogNotFoundException();
        }

        if (!$catalog->isEnabled()) {
            throw new CatalogDisabledException();
        }

        $productUuid = $query->getProductUuid();

        try {
            if (!$this->isProductBelongingToCatalogQuery->execute($catalog, $productUuid)) {
                throw new ProductNotFoundException();
            }

            return $this->getProductQuery->execute($catalog, $productUuid);
        } catch (\Exception $exception) {
            if (!($this->isCatalogValid)($catalog)) {
                $this->disableCatalogQuery->execute($catalog->getId());
                ($this->dispatchInvalidCatalogDisabledEvent)($catalog->getId());
                throw new CatalogDisabledException(previous: $exception);
            }

            throw $exception;
        }
    }
}
