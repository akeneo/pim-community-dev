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

namespace Akeneo\Platform\TailoredExport\Infrastructure\Validation\Operation;

use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\Attribute;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Platform\TailoredExport\Infrastructure\Validation\Measurement\UnitBelongsToMeasurementFamily;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\ConstraintValidator;

class MeasurementConversionOperationValidator extends ConstraintValidator
{
    public function __construct(
        private GetAttributes $getAttributes,
    ) {
    }

    public function validate($operation, Constraint $constraint): void
    {
        if (!$constraint instanceof MeasurementConversionOperationConstraint) {
            throw new \InvalidArgumentException('Invalid constraint');
        }

        $attribute = $this->getAttributes->forCode($constraint->attributeCode);

        if (!$attribute instanceof Attribute) {
            return;
        }

        $this->context->getValidator()
            ->inContext($this->context)
            ->validate($operation, new Collection([
                'fields' => [
                    'type' => new EqualTo(['value' => 'measurement_conversion']),
                    'target_unit_code' => new UnitBelongsToMeasurementFamily([
                        'measurementFamilyCode' => $attribute->metricFamily(),
                    ]),
                ],
            ]));
    }
}
