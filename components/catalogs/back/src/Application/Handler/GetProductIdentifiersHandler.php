<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Exception\CatalogNotFoundException;
use Akeneo\Catalogs\Application\Persistence\Catalog\DisableCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\GetCatalogQueryInterface;
use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductIdentifiersQueryInterface;
use Akeneo\Catalogs\Application\Service\DispatchInvalidCatalogDisabledEventInterface;
use Akeneo\Catalogs\Application\Validation\IsCatalogValidInterface;
use Akeneo\Catalogs\ServiceAPI\Exception\CatalogDisabledException;
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
        private GetCatalogQueryInterface $getCatalogQuery,
        private DisableCatalogQueryInterface $disableCatalogQuery,
        private IsCatalogValidInterface $isCatalogValid,
        private DispatchInvalidCatalogDisabledEventInterface $dispatchInvalidCatalogDisabledEvent,
    ) {
    }

    /**
     * @return array<string>
     *
     * @throws ServiceApiCatalogNotFoundException
     * @throws CatalogDisabledException
     */
    public function __invoke(GetProductIdentifiersQuery $query): array
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
            return $this->query->execute(
                $catalogDomain,
                $query->getSearchAfter(),
                $query->getLimit(),
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
