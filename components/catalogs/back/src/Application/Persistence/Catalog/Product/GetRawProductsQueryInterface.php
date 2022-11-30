<?php
declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog\Product;

use Akeneo\Catalogs\Domain\Catalog;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type RawProduct array{
 *      uuid: \Ramsey\Uuid\UuidInterface,
 *      identifier: string,
 *      is_enabled: boolean,
 *      product_model_code: string|null,
 *      created: \DateTimeImmutable,
 *      updated: \DateTimeImmutable,
 *      family_code: string|null,
 *      group_codes: array<string>,
 *      raw_values: array<string, array<string, array<string, string>>>,
 * }
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
