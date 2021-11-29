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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Event;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;

final class SelectOptionWasDeleted implements Event
{
    private string $attributeCode;
    private ColumnCode $columnCode;
    private SelectOptionCode $optionCode;

    public function __construct(string $attributeCode, ColumnCode $columnCode, SelectOptionCode $optionCode)
    {
        $this->attributeCode = $attributeCode;
        $this->columnCode = $columnCode;
        $this->optionCode = $optionCode;
    }

    public function attributeCode(): string
    {
        return $this->attributeCode;
    }

    public function columnCode(): ColumnCode
    {
        return $this->columnCode;
    }

    public function optionCode(): SelectOptionCode
    {
        return $this->optionCode;
    }
}
