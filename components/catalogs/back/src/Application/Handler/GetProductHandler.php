<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Handler;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetProductQueryInterface;
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
        private GetProductQueryInterface $getProductQuery,
    ) {
    }

    /**
     * @return Product|null
     */
    public function __invoke(GetProductQuery $query): ?array
    {
        return $this->getProductQuery->execute($query->getCatalogId(), $query->getProductUuid());
    }
}
