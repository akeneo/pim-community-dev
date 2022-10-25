<?php
declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Product;

use Akeneo\Catalogs\Domain\Catalog;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductInterface;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetProductsQueryInterface
{
    /**
     * @return array<ProductInterface>
     */
    public function execute(
        Catalog $catalog,
        ?string $searchAfter = null,
        int $limit = 100,
        ?string $updatedAfter = null,
        ?string $updatedBefore = null,
    ): array;
}
