<?php
declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Product;

use Akeneo\Catalogs\Domain\Catalog;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ProductValue array{
 *      scope: string|null,
 *      locale: string|null,
 *      data: mixed,
 * }
 *
 * @phpstan-type Product array{
 *      uuid: string,
 *      enabled: boolean,
 *      family: string,
 *      categories: array<string>,
 *      groups: array<string>,
 *      parent: string|null,
 *      values: array<string, ProductValue>,
 *      associations: array<string, array{groups: array<string>, products: array<string>, product_models: array<string>}>,
 *      quantified_associations: array<string, array{products: array<string>, product_models: array<string>}>,
 *      created: string,
 *      updated: string,
 * }
 */
interface GetProductsQueryInterface
{
    /**
     * @return array<Product>
     */
    public function execute(
        Catalog $catalog,
        ?string $searchAfter = null,
        int $limit = 100,
        ?string $updatedAfter = null,
        ?string $updatedBefore = null,
    ): array;
}
