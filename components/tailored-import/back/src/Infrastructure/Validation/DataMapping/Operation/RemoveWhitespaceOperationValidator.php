<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\RemoveWhitespaceOperation;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\RemoveWhitespaceOperation as RemoveWhitespaceOperationConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class RemoveWhitespaceOperationValidator extends ConstraintValidator
{
    public function validate($operation, Constraint $constraint): void
    {
        if (!$constraint instanceof RemoveWhitespaceOperationConstraint) {
            throw new UnexpectedTypeException($constraint, RemoveWhitespaceOperationConstraint::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($operation, new Collection([
            'fields' => [
                'uuid' => [new Uuid(), new NotBlank()],
                'type' => new EqualTo(RemoveWhitespaceOperation::TYPE),
                'modes' => new Choice(
                    choices: [
                        RemoveWhitespaceOperation::MODE_CONSECUTIVE,
                        RemoveWhitespaceOperation::MODE_TRIM,
                    ],
                    multiple: true,
                ),
            ],
        ]));
    }
}
