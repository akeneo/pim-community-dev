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

namespace Akeneo\Pim\TableAttribute\Domain\TableConfiguration;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnDataType;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\IsRequiredForCompleteness;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\MeasurementFamilyCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\MeasurementUnitCode;
use Webmozart\Assert\Assert;

final class MeasurementColumn extends AbstractColumnDefinition
{
    public const DATATYPE = 'measurement';

    final protected function __construct(
        ColumnId $id,
        ColumnCode $code,
        ColumnDataType $dataType,
        LabelCollection $labels,
        ValidationCollection $validations,
        IsRequiredForCompleteness $isRequiredForCompleteness,
        private MeasurementFamilyCode $measurementFamilyCode,
        private MeasurementUnitCode $measurementDefaultUnitCode
    ) {
        parent::__construct($id, $code, $dataType, $labels, $validations, $isRequiredForCompleteness);
    }

    /**
     * @param array<string, mixed> $normalized
     */
    public static function fromNormalized(array $normalized): self
    {
        Assert::keyExists($normalized, 'id');
        Assert::keyExists($normalized, 'code');
        Assert::keyExists($normalized, 'measurement_family_code');
        Assert::keyExists($normalized, 'measurement_default_unit_code');
        $dataType = ColumnDataType::fromString(static::DATATYPE);

        return new self(
            ColumnId::fromString($normalized['id']),
            ColumnCode::fromString($normalized['code']),
            $dataType,
            LabelCollection::fromNormalized($normalized['labels'] ?? []),
            ValidationCollection::fromNormalized($dataType, $normalized['validations'] ?? []),
            IsRequiredForCompleteness::fromBoolean($normalized['is_required_for_completeness'] ?? false),
            MeasurementFamilyCode::fromString($normalized['measurement_family_code']),
            MeasurementUnitCode::fromString($normalized['measurement_default_unit_code'])
        );
    }

    public function normalize(): array
    {
        $normalized = parent::normalize();
        $normalized['measurement_family_code'] = $this->measurementFamilyCode->asString();
        $normalized['measurement_default_unit_code'] = $this->measurementDefaultUnitCode->asString();

        return $normalized;
    }

    public function measurementFamilyCode(): MeasurementFamilyCode
    {
        return $this->measurementFamilyCode;
    }

    public function measurementDefaultUnitCode(): MeasurementUnitCode
    {
        return $this->measurementDefaultUnitCode;
    }
}
