<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog\Product;

use Akeneo\Catalogs\Domain\Catalog;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-import-type RawProduct from GetRawProductQueryInterface
 *
 */
interface GetRawProductsQueryInterface
{
    /**
     * @return array<RawProduct>
     */
    public function execute(
        Catalog $catalog,
        ?string $searchAfter = null,
        int $limit = 100,
        ?string $updatedAfter = null,
        ?string $updatedBefore = null,
    ): array;
}
