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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Tool\Bundle\MeasureBundle\ServiceApi\GetUnitTranslations;
use Webmozart\Assert\Assert;

class TableMeasurementTranslator implements TableValueTranslator
{
    public function __construct(private GetUnitTranslations $getUnitTranslations)
    {
    }

    public function getSupportedColumnDataType(): string
    {
        return MeasurementColumn::DATATYPE;
    }

    public function translate(string $attributeCode, ColumnDefinition $column, string $localeCode, string $value): string
    {
        Assert::isInstanceOf($column, MeasurementColumn::class);

        if (1 !== \preg_match('/^(?P<amount>([^ ]+)) (?P<unit>[^ ]+)$/', $value, $explodedValue)) {
            return $value;
        }
        $amount = $explodedValue['amount'];
        $unit = $explodedValue['unit'];

        $translatedUnits = $this->getUnitTranslations->byMeasurementFamilyCodeAndLocale($column->measurementFamilyCode()->asString(), $localeCode);

        return \sprintf('%s %s', $amount, $translatedUnits[$unit] ?? \sprintf('[%s]', $unit));
    }
}
