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

use Akeneo\Platform\TailoredImport\Domain\Model\Target\TargetInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class PropertyTargetValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof PropertyTarget) {
            throw new UnexpectedTypeException($constraint, PropertyTarget::class);
        }

        $this->context->getValidator()->inContext($this->context)->validate($value, [
            new Collection([
                'fields' => array_merge([
                    'code' => [
                        new Type('string'),
                        new NotBlank(),
                    ],
                    'locale' => new Blank(),
                    'channel' => new Blank(),
                    'type' => new EqualTo('property'),
                    'action_if_not_empty' => new EqualTo(TargetInterface::ACTION_SET),
                ], $constraint->getAdditionalConstraints()),
            ]),
        ]);
    }
}
