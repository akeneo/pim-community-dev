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

use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Repository\TableConfigurationRepository;
use Akeneo\Pim\TableAttribute\Domain\Value\Cell;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\Query\GetExistingRecordCodes;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class RecordsShouldExistValidator extends ConstraintValidator
{
    public function __construct(
        private TableConfigurationRepository $tableConfigurationRepository,
        private GetExistingRecordCodes $getExistingRecordCodes
    )
    {
    }

    public function validate($value, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, RecordsShouldExist::class);

        if (!$value instanceof TableValue) {
            return;
        }

        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($value->getAttributeCode());
        $indexedRecordColumns = [];
        foreach ($tableConfiguration->getRecordColumns() as $recordColumn) {
            $indexedRecordColumns[$recordColumn->id()->asString()] = $recordColumn;
        }
        if ($indexedRecordColumns === []) {
            return;
        }

        $indexedRecordCodes = [];
        foreach ($value->getData() as $rowIndex => $row) {
            /** @var Cell $cell */
            foreach ($row as $stringColumnId => $cell) {
                $recordColumn = $indexedRecordColumns[$stringColumnId] ?? null;
                if (null !== $recordColumn) {
                    $recordColumnIdentifier = $recordColumn->referenceEntityIdentifier()->asString();
                    $cellInformation = $rowIndex . '_' . $recordColumn->code()->asString() . '_' . $recordColumnIdentifier;
                    $indexedRecordCodes[$stringColumnId][$cellInformation] = $cell->normalize();
                }
            }
        }

        // @todo: is it possible to satisfy this condition ? how to test it ?
        if ($indexedRecordCodes === []) {
            return;
        }

        $existingRecordCodes = $this->getExistingRecordCodes->fromReferenceEntityIdentifierAndRecordCodes($indexedRecordCodes);

        foreach ($indexedRecordCodes as $stringColumnId => $recordCodes) {
            $nonExistingRecordCodes = array_diff($recordCodes, $existingRecordCodes[$stringColumnId] ?? []);
            foreach ($nonExistingRecordCodes as $cellCoordinates => $nonExistingRecordCode) {
                $cellData = explode('_', $cellCoordinates);
                $rowIndex = $cellData[0];
                $columnCode = $cellData[1];
                $referenceEntityIdentifier = $cellData[2];

                $this->context->buildViolation(
                    $constraint->message,
                    ['{{ recordCode }}' => $nonExistingRecordCode, '{{ referenceEntityIdentifier }}' => $referenceEntityIdentifier]
                )
                    ->atPath(sprintf('[%d].%s', $rowIndex, $columnCode))
                    ->addViolation();
            }
        }
    }
}
