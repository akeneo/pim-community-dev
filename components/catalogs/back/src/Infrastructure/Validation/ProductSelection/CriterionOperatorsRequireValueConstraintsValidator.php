<?php

declare(strict_types=1);

namespace Akeneo\Catalogs\Infrastructure\Validation\ProductSelection;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 *
 * @phpstan-type AttributeCriterion array{operator: string, value: mixed|null}
 */
final class CriterionOperatorsRequireValueConstraintsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        /** @var AttributeCriterion $value */

        if (!$constraint instanceof CriterionOperatorsRequireValueConstraints) {
            throw new UnexpectedTypeException($constraint, CriterionOperatorsRequireValueConstraints::class);
        }

        if (!\in_array($value['operator'], $constraint->operators)) {
            return;
        }

        /** @var iterable<ConstraintViolationInterface> $violations */
        $violations = $this->context->getValidator()->validate($value['value'], $constraint->constraints);

        foreach ($violations as $violation) {
            $this->context
                ->buildViolation((string) $violation->getMessage())
                ->atPath('[value]')
                ->addViolation();
        }
    }
}
