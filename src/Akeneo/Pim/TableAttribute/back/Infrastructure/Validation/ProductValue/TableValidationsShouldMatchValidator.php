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
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\Validation\MinValidation;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValidationCollection;
use Akeneo\Pim\TableAttribute\Domain\TableConfiguration\ValueObject\ColumnCode;
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
            foreach ($row as $stringColumnCode => $cell) {
                $constraints = $this->buildConstraints($tableConfiguration, ColumnCode::fromString($stringColumnCode));

                if (0 < count($constraints)) {
                    $validator
                        ->atPath(sprintf('[%d].%s', $rowIndex, $stringColumnCode))
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
        $columnDataType = $tableConfiguration->getColumnDataType($columnCode);
        $validations = $tableConfiguration->getValidations($columnCode);
        if (null === $columnDataType || null === $validations) {
            return [];
        }

        switch ($columnDataType->asString()) {
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
        $normalizedValidations = $validations->normalize();
        if (is_object($normalizedValidations)) {
            $normalizedValidations = [];
        }
        $maxLengthValue = $normalizedValidations['max_length'] ?? self::DEFAULT_MAX_LENGTH;

        return [
            new Constraints\Length([
                'max' => $maxLengthValue,
                'maxMessage' => TableValidationsShouldMatch::MAX_LENGTH_MESSAGE,
            ])
        ];
    }

    /**
     * @return Constraint[]
     *
     * TODO: test the value is a number or a string
     */
    private function buildConstraintsForNumber(ValidationCollection $validations): array
    {
        $constraints = [];
        $normalizedValidations = $validations->normalize();
        if (is_object($normalizedValidations)) {
            $normalizedValidations = [];
        }

        if (array_key_exists('min', $normalizedValidations)) {
            $constraints[] = new Constraints\Range([
                'min' => $normalizedValidations['min'],
                'minMessage' => TableValidationsShouldMatch::MIN_MESSAGE,
            ]);
        }

        if (array_key_exists('max', $normalizedValidations)) {
            $constraints[] = new Constraints\Range([
                'max' => $normalizedValidations['max'],
                'maxMessage' => TableValidationsShouldMatch::MAX_MESSAGE,
            ]);
        }

        $decimalsAllowedValue = array_key_exists('decimals_allowed', $normalizedValidations)
            ? $normalizedValidations['decimals_allowed']
            : self::DEFAULT_DECIMALS_ALLOWED;
        if (!$decimalsAllowedValue) {
            $constraints[] = new Constraints\Type([
                'type' => 'integer',
                'message' => TableValidationsShouldMatch::DECIMALS_ALLOWED_MESSAGE,
            ]);
        }

        return $constraints;
    }
}
