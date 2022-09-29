<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\Catalog\FindOneCatalogByIdQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductUuidsQueryInterface;
use Akeneo\Catalogs\Application\Service\DisableOnlyInvalidCatalogInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDoesNotExistException;
use Akeneo\Catalogs\ServiceAPI\Query\GetProductUuidsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
final class GetProductUuidsHandler
{
    public function __construct(
        private GetProductUuidsQueryInterface $query,
        private DisableOnlyInvalidCatalogInterface $disableOnlyInvalidCatalog,
        private FindOneCatalogByIdQueryInterface $findOneCatalogByIdQuery,
    ) {
    }

    /**
     * @return array<string>
     *
     * @throws CatalogDisabledException
     * @throws CatalogDoesNotExistException
     */
    public function __invoke(GetProductUuidsQuery $query): array
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
