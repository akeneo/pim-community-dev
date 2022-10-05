<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetCatalogProductValueFiltersQueryInterface
{
    /**
     * @return array{channels?: array<string>, locales?: array<string>, currencies?: array<string>}
     */
    public function execute(string $id): array;
}
