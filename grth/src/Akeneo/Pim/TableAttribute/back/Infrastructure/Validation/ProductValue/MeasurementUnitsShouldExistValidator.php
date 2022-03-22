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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\MeasurementColumn;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\MeasurementUnitExists;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class MeasurementUnitsShouldExistValidator extends ConstraintValidator
{
    public function __construct(
        private MeasurementUnitExists $measurementUnitExists,
        private TableConfigurationRepository $tableConfigurationRepository
    ) {
    }

    public function validate($value, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, MeasurementUnitsShouldExist::class);

        if (!$value instanceof TableValue) {
            return;
        }

        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($value->getAttributeCode());

        foreach ($value->getData() as $rowIndex => $row) {
            foreach ($row as $stringColumnId => $cell) {
                try {
                    $columnId = ColumnId::fromString($stringColumnId);
                } catch (\InvalidArgumentException) {
                    continue;
                }
                $column = $tableConfiguration->getColumn($columnId);

                if (!$column instanceof MeasurementColumn) {
                    continue;
                }

                $unit = $cell->normalize()['unit'] ?? null;

                if (!\is_string($unit)) {
                    continue;
                }

                $familyCode = $column->measurementFamilyCode()->asString();
                if (!$this->measurementUnitExists->inFamily($familyCode, $unit)) {
                    $this->context->buildViolation(
                        $constraint->message,
                        ['{{ measurement_family_code }}' => $familyCode, '{{ measurement_unit_code }}' => $unit]
                    )
                        ->atPath(sprintf('[%d].%s', $rowIndex, $column->code()->asString()))
                        ->addViolation();
                }
            }
        }
    }
}
