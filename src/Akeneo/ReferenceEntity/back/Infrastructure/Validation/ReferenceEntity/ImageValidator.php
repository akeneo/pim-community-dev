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

namespace Akeneo\ReferenceEntity\Infrastructure\Validation\ReferenceEntity;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Validation;

/**
 * @author    Christophe Chausseray <christophe.chausseray@akeneo.com>
 * @copyright 2018 Akeneo SAS (http://www.akeneo.com)
 */
class ImageValidator extends ConstraintValidator
{
    /**
     * @param mixed      $image       The value that should be validated
     * @param Constraint $constraint  The constraint for the validation
     */
    public function validate($image, Constraint $constraint)
    {
        if (!$constraint instanceof Image) {
            throw new UnexpectedTypeException($constraint, self::class);
        }

        $validator = Validation::createValidator();
        $violations = $validator->validate($image, [
                new Constraints\Type(['type' => 'array']),
                new Constraints\Collection(
                    [
                        'originalFilename' => [new Constraints\NotBlank(), new Constraints\Type('string')],
                        'filePath' => [new Constraints\NotBlank(), new Constraints\Type('string')],
                    ]
                )
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
