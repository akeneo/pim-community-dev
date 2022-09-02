<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Connector\FlatTranslator\Values;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ColumnDefinition;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ReferenceEntityColumn;
use Akeneo\Pim\TableAttribute\Domain\Value\Query\GetRecordLabel;
use Webmozart\Assert\Assert;

class TableRecordTranslator implements TableValueTranslator
{
    public function __construct(private GetRecordLabel $getRecordLabel)
    {
    }

    public function getSupportedColumnDataType(): string
    {
        return ReferenceEntityColumn::DATATYPE;
    }

    public function translate(string $attributeCode, ColumnDefinition $column, string $localeCode, string $value): string
    {
        Assert::isInstanceOf($column, ReferenceEntityColumn::class);

        return ($this->getRecordLabel)($column->referenceEntityIdentifier(), $value, $localeCode) ?? sprintf('[%s]', $value);
    }
}
