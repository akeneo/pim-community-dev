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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SearchAttributesQueryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SearchAttributesQuery) {
            throw new UnexpectedTypeException($constraint, SearchAttributesQuery::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $validator = $this->context->getValidator()->inContext($this->context);
        $validator->validate($value->request->all(), new Collection([
            'fields' => [
                'attribute_codes' => [
                    new Type('array'),
                    new All([
                        new Type('string'),
                    ]),
                ],
                'search' => [
                    new Type('string'),
                ],
                'locale_code' => [
                    new Type('string'),
                    new NotBlank(),
                ],
            ],
        ]));
    }
}
