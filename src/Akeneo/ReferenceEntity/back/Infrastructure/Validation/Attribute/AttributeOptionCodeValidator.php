<?php

declare(strict_types=1);

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeOption\OptionCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeOptionCodeValidator extends ConstraintValidator
{
    private const MAX_IDENTIFIER_LENGTH = 255;

    public function validate($code, Constraint $constraint)
    {
        if (!$constraint instanceof AttributeOptionCode) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($code, [
                new Constraints\NotBlank(['message' => AttributeOptionCode::CODE_SHOULD_NOT_BE_BLANK]),
                new Constraints\Type(['type' => 'string']),
                new Constraints\Length(['max' => self::MAX_IDENTIFIER_LENGTH, 'min' => 1]),
                new Constraints\Regex([
                        'pattern' => OptionCode::REGULAR_EXPRESSION,
                        'message' => AttributeOptionCode::MESSAGE_WRONG_PATTERN,
                    ]
                ),
            ]
        );

        foreach ($violations as $violation) {
            $this->context->addViolation($violation->getMessage(), $violation->getParameters());
        }
    }
}
