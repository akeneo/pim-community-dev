<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Catalog;

use Akeneo\Catalogs\Domain\Catalog;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface UpsertCatalogQueryInterface
{
    public function execute(Catalog $catalog): void;
}
