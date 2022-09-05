<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @phpstan-type AttributeCriterion array{operator: string, value: mixed|null}
 * @phpstan-type Attribute array{code: string, label: string, type: string, scopable: bool, localizable: bool}
 */
final class CriterionOperatorsRequireEmptyValueValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /** @var AttributeCriterion $value */

        if (!$constraint instanceof CriterionOperatorsRequireEmptyValue) {
            throw new UnexpectedTypeException($constraint, CriterionOperatorsRequireEmptyValue::class);
        }

        if (0 === \count($constraint->operators)) {
            throw new \LogicException('The operators option should not be empty');
        }

        if (\in_array($value['operator'], $constraint->operators) && !$this->isEmpty($value['value'])) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.value.not_empty')
                ->atPath('[value]')
                ->addViolation();
        }

        if (!\in_array($value['operator'], $constraint->operators) && $this->isEmpty($value['value'])) {
            $this->context
                ->buildViolation('akeneo_catalogs.validation.product_selection.criteria.value.empty')
                ->atPath('[value]')
                ->addViolation();
        }
    }

    private function isEmpty(mixed $value): bool
    {
        return null === $value || '' === $value || [] === $value;
    }
}
