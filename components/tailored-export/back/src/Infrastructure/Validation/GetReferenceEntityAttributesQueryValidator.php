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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class GetReferenceEntityAttributesQueryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof GetReferenceEntityAttributesQuery) {
            throw new UnexpectedTypeException($constraint, GetReferenceEntityAttributesQuery::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $this->context->getValidator()->inContext($this->context)
            ->atPath('[reference_entity_code]')
            ->validate(
                $value->get('reference_entity_code'),
                [new Type('string'), new NotBlank()],
            );
    }
}
