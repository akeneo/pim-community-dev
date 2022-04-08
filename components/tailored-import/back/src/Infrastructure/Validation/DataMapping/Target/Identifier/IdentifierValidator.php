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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Identifier;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\AttributeTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\DataMappingUuid;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\SampleData;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Sources;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Count;
use Symfony\Component\Validator\Constraints\IsNull;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IdentifierValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Identifier) {
            throw new UnexpectedTypeException($constraint, Identifier::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'uuid' => new DataMappingUuid(),
                'target' => new AttributeTarget([
                    'source_parameter' => new IsNull(),
                ]),
                // check if we pass array instead of two parameters
                'sources' => new Sources(false, $constraint->getColumns()),
                'operations' => new Count(['max' => 0]),
                'sample_data' => new SampleData(),
            ],
        ]));
    }
}
