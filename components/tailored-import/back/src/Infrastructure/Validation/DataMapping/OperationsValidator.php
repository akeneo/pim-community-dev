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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\OperationConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OperationsValidator extends ConstraintValidator
{
    public function __construct(
        private array $operationConstraints
    ) {
    }

    public function validate($operations, Constraint $constraint): void
    {
        if (!$constraint instanceof Operations) {
            throw new UnexpectedTypeException($constraint, Operations::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($operations, [
            new Type('array'),
        ]);

        if (0 < $this->context->getViolations()->count() || empty($operations)) {
            return;
        }

        foreach ($operations as $index => $operation) {
            $this->validateOperation($constraint, sprintf('[%s]', $index), $operation);
        }
    }

    private function validateOperation(Operations $operationsConstraint, string $path, array $operation): void
    {
        $operationType = $operation['type'];
        $compatibleOperationTypes = $operationsConstraint->getCompatibleOperations();

        if (!in_array($operationType, $compatibleOperationTypes)) {
            $this->context->buildViolation(Operations::INCOMPATIBLE_OPERATION_TYPE)
                ->setParameter('{{ operation_type }}', $operationType)
                ->atPath(sprintf('%s[type]', $path))
                ->addViolation();

            return;
        }

        $constraintClass = $this->operationConstraints[$operationType] ?? null;

        if (!$this->isOperationConstraint($constraintClass)) {
            return;
        }

        $operationConstraint = new $constraintClass();
        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->atPath($path)->validate($operation, $operationConstraint);
    }

    private function isOperationConstraint(string $constraintClass): bool
    {
        return is_subclass_of($constraintClass, OperationConstraint::class, true);
    }
}
