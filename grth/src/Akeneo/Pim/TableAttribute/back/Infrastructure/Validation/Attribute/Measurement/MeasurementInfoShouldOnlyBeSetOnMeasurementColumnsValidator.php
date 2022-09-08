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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class MeasurementInfoShouldOnlyBeSetOnMeasurementColumnsValidator extends ConstraintValidator
{
    public function validate($columnData, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, MeasurementInfoShouldOnlyBeSetOnMeasurementColumns::class);
        if (!is_array($columnData) || !is_string($columnData['data_type'] ?? null)) {
            return;
        }

        $columnType = $columnData['data_type'];

        if (MeasurementColumn::DATATYPE === $columnType) {
            if (!isset($columnData['measurement_family_code'])) {
                $this->context->buildViolation(
                    'pim_table_configuration.validation.table_configuration.measurement_family_code_must_be_filled'
                )->addViolation();
            }
            if (!isset($columnData['measurement_default_unit_code'])) {
                $this->context->buildViolation(
                    'pim_table_configuration.validation.table_configuration.measurement_default_unit_code_must_be_filled'
                )->addViolation();
            }
        } else {
            if (isset($columnData['measurement_family_code'])) {
                $this->context->buildViolation(
                    'pim_table_configuration.validation.table_configuration.measurement_family_code_cannot_be_set',
                    ['{{ data_type }}' => $columnType]
                )->addViolation();
            }
            if (isset($columnData['measurement_default_unit_code'])) {
                $this->context->buildViolation(
                    'pim_table_configuration.validation.table_configuration.measurement_default_unit_code_cannot_be_set',
                    ['{{ data_type }}' => $columnType]
                )->addViolation();
            }
        }
    }
}
