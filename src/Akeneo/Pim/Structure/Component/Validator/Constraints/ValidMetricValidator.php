<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Value\MetricValueInterface;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Tool\Bundle\MeasureBundle\Provider\LegacyMeasurementProvider;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Metric attribute validator
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidMetricValidator extends ConstraintValidator
{
    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /** @var LegacyMeasurementProvider */
    private $legacyMeasureProvider;

    public function __construct(PropertyAccessorInterface $propertyAccessor, LegacyMeasurementProvider $provider)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->legacyMeasureProvider = $provider;
    }

    /**
     * Validate metric type and default metric unit
     *
     * @param AttributeInterface|MetricInterface|ValueInterface $object
     * @param Constraint                                        $constraint
     *
     * @throws \Exception
     */
    public function validate($object, Constraint $constraint)
    {
        if (!$constraint instanceof ValidMetric) {
            throw new UnexpectedTypeException($constraint, ValidMetric::class);
        }

        if ($object instanceof AttributeInterface) {
            $familyProperty = 'metricFamily';
            $unitProperty = 'defaultMetricUnit';
        } elseif ($object instanceof MetricInterface && null !== $object->getData()) {
            $familyProperty = 'family';
            $unitProperty = 'unit';
        } elseif ($object instanceof MetricValueInterface && null !== $object->getData()
            && (null !== $object->getUnit() || null !== $object->getAmount())
        ) {
            $object = $object->getData();
            $familyProperty = 'family';
            $unitProperty = 'unit';
        } else {
            return;
        }

        $measureFamilies = $this->legacyMeasureProvider->getMeasurementFamilies();
        $family = $this->propertyAccessor->getValue($object, $familyProperty);
        $unit = $this->propertyAccessor->getValue($object, $unitProperty);
        if (!array_key_exists($family, $measureFamilies)) {
            $this->context->buildViolation($constraint->familyMessage)
                ->atPath($familyProperty)
                ->addViolation();
        } elseif (!array_key_exists($unit, $measureFamilies[$family]['units'])) {
            $this->context->buildViolation($constraint->unitMessage)
                ->atPath($unitProperty)
                ->addViolation();
        }
    }
}
