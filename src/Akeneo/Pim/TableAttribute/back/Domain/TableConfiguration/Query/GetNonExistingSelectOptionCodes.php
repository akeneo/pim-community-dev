<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;

interface GetNonExistingSelectOptionCodes
{
    /**
     * @param array<SelectOptionCode> $selectOptionCodes
     *
     * @return array<SelectOptionCode>
     */
    public function forOptionCodes(string $attributeCode, ColumnCode $columnCode, array $selectOptionCodes): array;
}
