<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCatalogProductSelectionCriteriaQueryInterface
{
    /**
     * @return array<array-key, array{field: string, operator: string, value?: mixed, scope?: string|null, locale?: string|null}>
     */
    public function execute(string $id): array;
}
