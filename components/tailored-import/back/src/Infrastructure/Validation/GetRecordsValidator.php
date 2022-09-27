<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class GetRecordsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof GetRecords) {
            throw new UnexpectedTypeException($constraint, GetRecords::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $test = $value->request->all();

        $validator = $this->context->getValidator()->inContext($this->context);
        $validator->validate($value->request->all(), new Collection([
            'fields' => [
                'search' => [
                    new Type('string'),
                ],
                'locale' => [
                    new Type('string'),
                    new NotNull(),
                ],
                'channel' => [
                    new Type('string'),
                    new NotNull(),
                ],
                'include_codes' => [
                    new Type('string'),
                ],
                'exclude_codes' => [
                    new Type('string'),
                ],
            ],
        ], allowExtraFields: true));
        $validator->validate($value->request->get('reference_entity_code'), new Type('string'));
    }
}
