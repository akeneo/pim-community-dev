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

namespace Akeneo\Pim\TableAttribute\Infrastructure\Validation\ProductValue;

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Query\GetNonExistingSelectOptionCodes;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\SelectOptionCode;
use Akeneo\Pim\TableAttribute\Domain\Value\Cell;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class SelectOptionsShouldExistValidator extends ConstraintValidator
{
    private TableConfigurationRepository $tableConfigurationRepository;
    private GetNonExistingSelectOptionCodes $getNonExistingSelectOptionCodes;

    public function __construct(
        TableConfigurationRepository $tableConfigurationRepository,
        GetNonExistingSelectOptionCodes $getNonExistingSelectOptionCodes
    ) {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
        $this->getNonExistingSelectOptionCodes = $getNonExistingSelectOptionCodes;
    }

    public function validate($tableValue, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, SelectOptionsShouldExist::class);
        if (!$tableValue instanceof TableValue) {
            return;
        }

        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($tableValue->getAttributeCode());
        $indexedSelectColumns = [];
        foreach ($tableConfiguration->getSelectColumns() as $selectColumn) {
            $indexedSelectColumns[$selectColumn->id()->asString()] = $selectColumn;
        }

        $table = $tableValue->getData();

        $optionCodesPerColumnId = [];
        foreach ($table as $rowIndex => $row) {
            /** @var Cell $cell */
            foreach ($row as $stringColumnId => $cell) {
                $selectColumn = $indexedSelectColumns[$stringColumnId] ?? null;
                $data = $cell->normalize();
                if (null === $selectColumn || !is_string($data)) {
                    continue;
                }

                $optionCodesPerColumnId[$stringColumnId][$rowIndex] = $data;
            }
        }

        foreach ($optionCodesPerColumnId as $stringColumnId => $optionCodes) {
            try {
                $columnId = ColumnId::fromString($stringColumnId);
            } catch (\Exception $e) {
                continue;
            }
            $column = $tableConfiguration->getColumn($columnId);
            if (null === $column) {
                continue;
            }

            $optionCodeObjects = array_map(
                fn (string $optionCode): SelectOptionCode => SelectOptionCode::fromString((string) $optionCode),
                array_values(array_unique($optionCodes))
            );
            $nonExistingOptions = $this->getNonExistingSelectOptionCodes->forOptionCodes(
                $tableValue->getAttributeCode(),
                $column->code(),
                $optionCodeObjects
            );
            $nonExistingOptions = array_map(
                fn (SelectOptionCode $optionCode): string => $optionCode->asString(),
                $nonExistingOptions
            );
            foreach ($optionCodes as $rowIndex => $optionCode) {
                if (in_array($optionCode, $nonExistingOptions)) {
                    $this->context->buildViolation($constraint->message, ['{{ optionCode }}' => $optionCode])
                        ->atPath(sprintf('[%d].%s', $rowIndex, $column->code()->asString()))
                        ->addViolation();
                }
            }
        }
    }
}
