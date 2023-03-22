<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Application\Persistence\Family;

/**
 * @copyright 2023 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface GetFamilyLabelByCodeAndLocaleQueryInterface
{
    public function execute(string $code, string $locale): string;
}
