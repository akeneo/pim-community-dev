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

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\IsValidAttribute;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class AttributeTargetValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof AttributeTarget) {
            throw new UnexpectedTypeException($constraint, AttributeTarget::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, [
            new Collection([
                'fields' => array_merge([
                    'code' => [
                        new Type('string'),
                        new NotBlank(),
                    ],
                    'locale' => new Type('string'),
                    'channel' => new Type('string'),
                    'type' => new EqualTo('attribute'),
                    'attribute_type' => new Type('string'),
                    'reference_data_name' => new Optional(new Type('string')),
                    'source_configuration' => new Type('array'),
                    'action_if_not_empty' => new Type('string'),
                    'action_if_empty' => new Type('string'),
                ], $constraint->getAdditionalConstraints()),
            ]),
            new IsValidAttribute(),
        ]);
    }
}
