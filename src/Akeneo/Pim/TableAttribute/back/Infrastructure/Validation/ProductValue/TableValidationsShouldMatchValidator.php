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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\TableConfiguration;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation\DecimalsAllowedValidation;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation\MaxLengthValidation;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation\MaxValidation;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation\MinValidation;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValidationCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnId;
use Akeneo\Pim\TableAttribute\Domain\Value\Cell;
use Akeneo\Pim\TableAttribute\Infrastructure\Value\TableValue;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints as Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class TableValidationsShouldMatchValidator extends ConstraintValidator
{
    private const DEFAULT_MAX_LENGTH = 100;
    private const DEFAULT_DECIMALS_ALLOWED = false;

    private TableConfigurationRepository $tableConfigurationRepository;

    public function __construct(TableConfigurationRepository $tableConfigurationRepository)
    {
        $this->tableConfigurationRepository = $tableConfigurationRepository;
    }

    public function validate($tableValue, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, TableValidationsShouldMatch::class);
        if (!$tableValue instanceof TableValue) {
            return;
        }

        $tableConfiguration = $this->tableConfigurationRepository->getByAttributeCode($tableValue->getAttributeCode());
        $context = $this->context;
        $validator = $context->getValidator()->inContext($context);

        $table = $tableValue->getData();
        foreach ($table as $rowIndex => $row) {
            /** @var Cell $cell */
            foreach ($row as $stringColumnId => $cell) {
                try {
                    $columnId = ColumnId::fromString($stringColumnId);
                } catch (\Exception $e) {
                    continue;
                }

                $column = $tableConfiguration->getColumn($columnId);
                if (null === $column) {
                    continue;
                }
                $constraints = $this->buildConstraints($tableConfiguration, $column->code());

                if (0 < count($constraints)) {
                    $validator
                        ->atPath(sprintf('[%d].%s', $rowIndex, $column->code()->asString()))
                        ->validate($cell->normalize(), $constraints);
                }
            }
        }
    }

    /**
     * @return Constraint[]
     */
    private function buildConstraints(TableConfiguration $tableConfiguration, ColumnCode $columnCode): array
    {
        $column = $tableConfiguration->getColumnByCode($columnCode);
        $validations = $tableConfiguration->getValidations($columnCode);
        if (null === $column || null === $validations) {
            return [];
        }

        switch ($column->dataType()->asString()) {
            case 'text':
                return $this->buildConstraintsForText($validations);
            case 'number':
                return $this->buildConstraintsForNumber($validations);
            default:
                return [];
        }
    }

    /**
     * @return Constraint[]
     */
    private function buildConstraintsForText(ValidationCollection $validations): array
    {
        $maxLengthValue = self::DEFAULT_MAX_LENGTH;
        foreach ($validations as $validation) {
            if ($validation instanceof MaxLengthValidation) {
                $maxLengthValue = $validation->getValue();
            }
        }

        return [
            new Constraints\Length(
                [
                    'max' => $maxLengthValue,
                    'maxMessage' => TableValidationsShouldMatch::MAX_LENGTH_MESSAGE,
                ]
            ),
        ];
    }

    /**
     * @return Constraint[]
     */
    private function buildConstraintsForNumber(ValidationCollection $validations): array
    {
        $constraints = [];
        $decimalsAllowedValue = self::DEFAULT_DECIMALS_ALLOWED;

        foreach ($validations as $validation) {
            if ($validation instanceof MinValidation) {
                $constraints[] = new Constraints\Range(
                    [
                        'min' => $validation->getValue(),
                        'minMessage' => TableValidationsShouldMatch::MIN_MESSAGE,
                    ]
                );
            } elseif ($validation instanceof MaxValidation) {
                $constraints[] = new Constraints\Range(
                    [
                        'max' => min(PHP_INT_MAX, $validation->getValue()),
                        'maxMessage' => TableValidationsShouldMatch::MAX_MESSAGE,
                    ]
                );
            } elseif ($validation instanceof DecimalsAllowedValidation) {
                $decimalsAllowedValue = $validation->getValue();
            }
        }

        if (!$decimalsAllowedValue) {
            $constraints[] = new NotDecimal(
                [
                    'message' => TableValidationsShouldMatch::DECIMALS_ALLOWED_MESSAGE,
                ]
            );
        }

        return $constraints;
    }
}
