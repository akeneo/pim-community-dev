<?php

declare(strict_types=1);

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\SampleData;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\SampleData;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\FileKey;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RefreshSampleDataQueryValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof RefreshSampleDataQuery) {
            throw new UnexpectedTypeException($constraint, RefreshSampleDataQuery::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $this->context->getValidator()->inContext($this->context)->validate($value->query->all(), new Collection([
            'file_key' => [
                new FileKey(),
            ],
            'column_indices' => [
                new All([
                    new Type('digit'),
                ]),
            ],
            'sheet_name' => [
                new Type('string'),
                new NotBlank(['allowNull' => true]),
            ],
            'product_line' => new Type('digit'),
            'current_sample' => new Optional(new SampleData()),
        ]));
    }
}
