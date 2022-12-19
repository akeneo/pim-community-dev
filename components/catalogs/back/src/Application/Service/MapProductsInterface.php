<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Service;

use Akeneo\Catalogs\Application\Persistence\Catalog\Product\GetRawProductsQueryInterface;
use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Catalogs\ServiceAPI\Query\GetMappedProductsQuery;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type MappedProduct from GetMappedProductsQuery
 * @phpstan-import-type RawProduct from GetRawProductsQueryInterface
 */
interface MapProductsInterface
{
    /**
     * @param array<RawProduct> $products
     *
     * @return array<MappedProduct>
     */
    public function __invoke(array $products, Catalog $catalog): array;
}
