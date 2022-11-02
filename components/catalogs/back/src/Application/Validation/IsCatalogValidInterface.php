<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Validation;

use Akeneo\Catalogs\Domain\Catalog;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface IsCatalogValidInterface
{
    public function __invoke(Catalog $catalog): bool;
}
