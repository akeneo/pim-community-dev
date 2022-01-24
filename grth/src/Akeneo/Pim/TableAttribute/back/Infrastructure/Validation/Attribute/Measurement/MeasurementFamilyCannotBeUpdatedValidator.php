<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2022 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\Attribute\Measurement;

use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationNotFoundException;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\MeasurementFamilyCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class MeasurementFamilyCannotBeUpdatedValidator extends ConstraintValidator
{
    public function __construct(private TableConfigurationRepository $tableConfigurationRepository)
    {
    }

    public function validate($column, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, MeasurementFamilyCannotBeUpdated::class);

        $columnCode = $column['code'] ?? null;
        $columnType = $column['data_type'] ?? null;
        $newMeasurementFamilyCode = $column['measurement_family_code'] ?? null;
        if (!\is_string($columnCode)
            || !\is_string($newMeasurementFamilyCode)
            || $columnType !== MeasurementColumn::DATATYPE
        ) {
            return;
        }

        try {
            $columnCode = ColumnCode::fromString($columnCode);
            $newMeasurementFamilyCode = MeasurementFamilyCode::fromString($newMeasurementFamilyCode);
        } catch (\InvalidArgumentException) {
            return;
        }

        $attribute = $this->context->getRoot();
        if (!$attribute instanceof AttributeInterface || !\is_string($attribute->getCode())) {
            return;
        }

        try {
            $formerTableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($attribute->getCode());
        } catch (TableConfigurationNotFoundException) {
            return;
        }

        $formerColumn = $formerTableConfiguration->getColumnByCode($columnCode);
        if ($formerColumn instanceof MeasurementColumn
            && !$formerColumn->measurementFamilyCode()->equals($newMeasurementFamilyCode)
        ) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '{{ column_code }}' => $columnCode->asString(),
                    '{{ given_measurement_family_code }}' => $newMeasurementFamilyCode->asString(),
                    '{{ former_measurement_family_code }}' => $formerColumn->measurementFamilyCode()->asString(),
                ]
            )
                ->atPath('measurement_family_code')
                ->addViolation();
        }
    }
}
