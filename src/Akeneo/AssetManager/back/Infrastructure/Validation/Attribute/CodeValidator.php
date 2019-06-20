<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2018 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\Attribute;

use Akeneo\ReferenceEntity\Domain\Model\Attribute\AttributeCode;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 * @copyright 2018 Akeneo SAS (https://www.akeneo.com)
 */
class CodeValidator extends ConstraintValidator
{
    private const MAX_IDENTIFIER_LENGTH = 255;

    public function validate($code, Constraint $constraint)
    {
        if (!$constraint instanceof Code) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($code, [
                new Constraints\NotBlank(),
                new Constraints\Type(['type' => 'string']),
                new Constraints\Length(['max' => self::MAX_IDENTIFIER_LENGTH, 'min' => 1]),
                new Constraints\Regex([
                        'pattern' => '/^[a-zA-Z0-9_]+$/',
                        'message' => Code::MESSAGE_WRONG_PATTERN,
                    ]
                ),
                new Constraints\Callback(function ($value, ExecutionContextInterface $context, $payload) {
                    if (in_array(strtolower($value), AttributeCode::RESERVED_CODES)) {
                        $context->buildViolation(Code::MESSAGE_RESERVED_CODE)
                            ->setParameter(
                                'comma_separated_reserved_keywords',
                                implode(', ', AttributeCode::RESERVED_CODES)
                            )
                            ->addViolation();
                    }
                }),
            ]
        );

        if ($violations->count() > 0) {
            foreach ($violations as $violation) {
                $this->context->addViolation(
                    $violation->getMessage(),
                    $violation->getParameters()
                );
            }
        }
    }
}
