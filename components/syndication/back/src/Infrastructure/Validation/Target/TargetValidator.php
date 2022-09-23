<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2021 Akeneo SAS (https://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Platform\Syndication\Infrastructure\Validation\Target;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;

class TargetValidator extends ConstraintValidator
{
    private const TARGET_MAX_LENGTH = 255;

    public function validate($target, Constraint $constraint): void
    {
        $this->context->getValidator()
            ->inContext($this->context)
            ->validate($target, new Collection([
                'type' => new Choice([
                    'choices' => ['string', 'boolean', 'number', 'measurement', 'string_collection', 'limited_string', 'price', 'url'],
                ]),
                'required' => new Type('bool'),
                'name' => [
                    new Type('string'),
                    new NotBlank(['message' => Target::TARGET_SHOULD_NOT_BE_BLANK]),
                    new Length([
                        'max' => self::TARGET_MAX_LENGTH,
                        'maxMessage' => Target::TARGET_MAX_LENGTH_REACHED
                    ])
                ],
            ]));
    }
}
