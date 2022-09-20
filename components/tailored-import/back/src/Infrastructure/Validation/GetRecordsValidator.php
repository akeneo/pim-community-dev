<?php

declare(strict_types=1);

/**
 * @copyright 2022 Akeneo SAS (https://www.akeneo.com)
 * @license   https://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation;

use Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints\NotBlank;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class GetRecordsValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof GetRecords) {
            throw new UnexpectedTypeException($constraint, GetRecords::class);
        }

        if (!$value instanceof Request) {
            return;
        }

        $validator = $this->context->getValidator()->inContext($this->context);
        $validator->validate($value->request->all(), new Collection([
            'fields' => [
                'reference_entity_code' => [
                    new Type('string'),
                    new NotBlank(),
                ],
                'search' => [
                    new Type('string'),
                ],
                'locale' => [
                    new Type('string'),
                    new NotBlank(),
                ],
                'channel' => [
                    new Type('string'),
                    new NotBlank(),
                ],
                'include_codes' => [
                    new Type('string'),
                ],
                'exclude_codes' => [
                    new Type('string'),
                ]
            ],
        ], allowExtraFields: true));
    }
}
