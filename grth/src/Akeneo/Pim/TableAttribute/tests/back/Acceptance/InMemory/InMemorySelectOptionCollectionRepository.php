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

namespace Akeneo\Test\Pim\TableAttribute\Acceptance\InMemory;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\SelectOptionCollectionRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\SelectOptionCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\WriteSelectOptionCollection;

class InMemorySelectOptionCollectionRepository implements SelectOptionCollectionRepository
{
    /** @var array<string, array<string, SelectOptionCollection>>  */
    private array $options = [];

    public function save(
        string $attributeCode,
        ColumnCode $columnCode,
        WriteSelectOptionCollection $selectOptionCollection
    ): void {
        $this->options[$attributeCode][$columnCode->asString()] = SelectOptionCollection::fromNormalized(
            $selectOptionCollection->normalize()
        );
    }

    public function getByColumn(string $attributeCode, ColumnCode $columnCode): SelectOptionCollection
    {
        return $this->options[\strtolower($attributeCode)][\strtolower($columnCode->asString())] ?? SelectOptionCollection::empty();
    }

    public function upsert(
        string $attributeCode,
        ColumnCode $columnCode,
        SelectOptionCollection $selectOptionCollection
    ): void {
        $formerOptions = $this->options[\strtolower($attributeCode)][\strtolower($columnCode->asString())] ?? SelectOptionCollection::empty();
        $this->options[\strtolower($attributeCode)][\strtolower($columnCode->asString())] = SelectOptionCollection::fromNormalized(
            [...$formerOptions->normalize(), ...$selectOptionCollection->normalize()]
        );
    }
}
