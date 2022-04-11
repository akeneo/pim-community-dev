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

namespace Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Target\Measurement;

use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\AttributeTarget;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\DataMappingUuid;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Operations;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\SampleData;
use Akeneo\Platform\TailoredImport\Infrastructure\Validation\DataMapping\Sources;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

final class MeasurementValidator extends ConstraintValidator
{
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof Measurement) {
            throw new UnexpectedTypeException($constraint, Measurement::class);
        }

        $validator = $this->context->getValidator();
        $validator->inContext($this->context)->validate($value, new Collection([
            'fields' => [
                'uuid' => new DataMappingUuid(),
                'target' => new AttributeTarget([
                    // TODO pluralize source_parameter everywhere in the codebase
                    'source_parameter' => new MeasurementSourceParameter($constraint->getAttribute()->metricFamily()),
                ]),
                'sources' => new Sources(false, $constraint->getColumnUuids()),
                'operations' => new Operations([]),
                'sample_data' => new SampleData(),
            ],
        ])); // TODO implement it with Axel
    }
}
