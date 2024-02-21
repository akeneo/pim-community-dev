<?php

declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Type as BaseType;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author    Thomas Fehringer <thomas.fehringer@getakeneo.com>
 * @copyright 2022 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class TypeValidator extends ConstraintValidator
{
    public function __construct(
        private ValidatorInterface $validator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Type) {
            throw new UnexpectedTypeException($constraint, Type::class);
        }

        $violations = $this->validator->validate(
            $value,
            [new BaseType(['type' => $constraint->type, 'message' => $constraint->message])]
        );

        if (0 === $violations->count()) {
            return;
        }

        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            if (!$violation->getConstraint() instanceof BaseType) {
                continue;
            }
            $this->context
                ->buildViolation(
                    $constraint->message,
                    array_merge($violation->getParameters(), ['{{ givenType }}' => getType($value)])
                )
                ->addViolation();
        }
    }
}
