<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Automation\RuleEngine\Component\Validator;

use Akeneo\Pim\Automation\RuleEngine\Component\Validator\Constraint\MeasurementUnit;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Query\PublicApi\AttributeType\GetAttributes;
use Akeneo\Tool\Bundle\MeasureBundle\Manager\MeasureManager;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Webmozart\Assert\Assert;

final class MeasurementUnitValidator extends ConstraintValidator
{
    /** @var PropertyAccessorInterface */
    private $propertyAccessor;

    /** @var MeasureManager */
    private $measureManager;

    /** @var GetAttributes */
    private $getAttributes;

    public function __construct(
        PropertyAccessorInterface $propertyAccessor,
        MeasureManager $measureManager,
        GetAttributes $getAttributes
    ) {
        $this->propertyAccessor = $propertyAccessor;
        $this->measureManager = $measureManager;
        $this->getAttributes = $getAttributes;
    }

    public function validate($object, Constraint $constraint)
    {
        Assert::isInstanceOf($constraint, MeasurementUnit::class);

        $unit = $this->propertyAccessor->getValue($object, $constraint->unitProperty);
        if (!is_string($unit)) {
            return;
        }

        $attributeCode = $this->propertyAccessor->getValue($object, $constraint->attributeProperty);
        if (!is_string($attributeCode)) {
            return;
        }

        $attribute = $this->getAttributes->forCode($attributeCode);
        if (null === $attribute) {
            return;
        }

        if (AttributeTypes::METRIC !== $attribute->type()) {
            $this->context->buildViolation(
                $constraint->notMetricAttributeMessage,
                [
                    '{{ attributeCode }}' => $attributeCode,
                    '{{ unitCode }}' => $unit,
                ]
            )->atPath($constraint->unitProperty)->addViolation();
        } elseif (!$this->measureManager->unitCodeExistsInFamily($unit, $attribute->metricFamily())) {
            $this->context->buildViolation(
                $constraint->invalidUnitMessage,
                [
                    '{{ attributeCode }}' => $attributeCode,
                    '{{ unitCode }}' => $unit,
                ]
            )->atPath($constraint->unitProperty)->addViolation();
        }
    }
}
