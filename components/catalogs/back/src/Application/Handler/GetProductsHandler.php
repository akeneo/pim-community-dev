<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductsQueryInterface;
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
    ) {
    }

    /**
     * @return array<Product>
     */
    public function __invoke(GetProductsQuery $query): array
    {
        return $this->query->execute(
            $query->getCatalogId(),
            $query->getSearchAfter(),
            $query->getLimit(),
            $query->getUpdatedAfter(),
            $query->getUpdatedBefore(),
        );
    }
}
