<?php
declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCatalogIdsUsingCurrenciesAsFilterQueryInterface
{
    /**
     * @param array<string> $currencyCodes
     * @return array<string>
     */
    public function execute(array $currencyCodes): array;
}
