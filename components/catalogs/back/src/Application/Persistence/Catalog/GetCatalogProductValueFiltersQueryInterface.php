<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @phpstan-type ProductValueFilters array{
 *      channels?: array<string>|null,
 *      locales?: array<string>|null,
 *      currencies?: array<string>|null,
 * }
 */
interface GetCatalogProductValueFiltersQueryInterface
{
    /**
     * @return ProductValueFilters
     */
    public function execute(string $id): array;
}
