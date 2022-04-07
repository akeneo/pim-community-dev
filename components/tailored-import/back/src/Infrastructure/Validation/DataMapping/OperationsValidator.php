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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class OperationsValidator extends ConstraintValidator
{
    private const MAX_OPERATION_COUNT = 5;

    public function validate($operations, Constraint $constraint): void
    {
        if (!$constraint instanceof Operations) {
            throw new UnexpectedTypeException($constraint, Operations::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($operations, [
            new Type('array'),
            new Count([
                'max' => self::MAX_OPERATION_COUNT,
                'maxMessage' => Operations::MAX_COUNT_REACHED,
            ])
        ]);

        if (0 < $this->context->getViolations()->count() || empty($operations)) {
            return;
        }

        foreach ($operations as $operation) {
            $this->validateOperation($validator, $operation);
        }
    }

    private function validateOperation(ValidatorInterface $validator, array $operation) {

    }
}
