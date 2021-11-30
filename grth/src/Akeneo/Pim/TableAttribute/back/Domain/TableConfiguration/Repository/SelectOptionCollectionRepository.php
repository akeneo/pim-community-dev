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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;

interface SelectOptionCollectionRepository
{
    public function save(
        string $attributeCode,
        ColumnCode $columnCode,
        WriteSelectOptionCollection $selectOptionCollection
    ): void;

    public function getByColumn(string $attributeCode, ColumnCode $columnCode): SelectOptionCollection;

    public function upsert(string $attributeCode, ColumnCode $columnCode, SelectOptionCollection $selectOptionCollection): void;
}
