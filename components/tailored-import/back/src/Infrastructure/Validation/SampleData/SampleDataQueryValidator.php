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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\SampleData;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\FileKey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class SampleDataQueryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof SampleDataQuery) {
            throw new UnexpectedTypeException($constraint, SampleDataQuery::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $this->context->getValidator()->inContext($this->context)->validate($value->query->all(), new Collection([
            'file_key' => new FileKey(),
            'column_indices' => new All([new Type('digit')]),
            'sheet_name' => [
                new Type('string'),
                new NotBlank(['allowNull' => true]),
            ],
            'product_line' => new Type('digit'),
        ]));
    }
}
