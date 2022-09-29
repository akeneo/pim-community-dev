<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\Catalog\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductsQueryInterface;
use Akeneo\Catalogs\Application\Service\DisableOnlyInvalidCatalogInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDoesNotExistException;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type Product from GetProductsQueryInterface
 */
final class GetProductsHandler
{
    public function __construct(
        private GetProductsQueryInterface $query,
        private DisableOnlyInvalidCatalogInterface $disableOnlyInvalidCatalog,
        private FindOneCatalogByIdQueryInterface $findOneCatalogByIdQuery,
    ) {
    }

    /**
     * @return array<Product>
     *
     * @throws CatalogDisabledException
     * @throws CatalogDoesNotExistException
     */
    public function __invoke(GetProductsQuery $query): array
    {
        $catalog = $this->findOneCatalogByIdQuery->execute($query->getCatalogId());
        if (null === $catalog) {
            throw new CatalogDoesNotExistException();
        }

        if (!$catalog->isEnabled()) {
            throw new CatalogDisabledException();
        }

        try {
            return $this->query->execute(
                $query->getCatalogId(),
                $query->getSearchAfter(),
                $query->getLimit(),
                $query->getUpdatedAfter(),
                $query->getUpdatedBefore(),
            );
        } catch (\Exception $exception) {
            $isCatalogDisabled = $this->disableOnlyInvalidCatalog->disable($catalog);
            if ($isCatalogDisabled) {
                throw new CatalogDisabledException(previous: $exception);
            }
            throw $exception;
        }
    }
}
