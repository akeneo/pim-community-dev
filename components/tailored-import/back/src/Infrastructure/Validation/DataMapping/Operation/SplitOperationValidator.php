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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation;

use Akeneo\Platform\TailoredImport\Domain\Model\Operation\SplitOperation;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operation\SplitOperation as SplitOperationConstraint;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class SplitOperationValidator extends ConstraintValidator
{
    public function __construct(
        private array $availableSeparators,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SplitOperationConstraint) {
            throw new UnexpectedTypeException($constraint, SplitOperationConstraint::class);
        }

        $this->context->getValidator()
            ->inContext($this->context)
            ->validate($value, new Collection([
                'fields' => [
                    'uuid' => [new Uuid(), new NotBlank()],
                    'type' => new EqualTo(['value' => SplitOperation::TYPE]),
                    'separator' => new Choice($this->availableSeparators),
                ],
            ]));
    }
}
