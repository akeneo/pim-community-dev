<?php

declare(strict_types=1);

namespace Akeneo\AssetManager\Infrastructure\Validation\Attribute;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Akeneo\AssetManager\Domain\Model\Attribute\AttributeOption\OptionCode;
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
                new NotBlank(['message' => AttributeOptionCode::CODE_SHOULD_NOT_BE_BLANK]),
                new Type(['type' => 'string']),
                new Length(['max' => self::MAX_IDENTIFIER_LENGTH, 'min' => 1]),
                new Regex([
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
