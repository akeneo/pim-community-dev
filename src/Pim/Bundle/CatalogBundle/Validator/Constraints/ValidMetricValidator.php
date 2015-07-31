<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\AttributeInterface;
use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Metric attribute validator
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ValidMetricValidator extends ConstraintValidator
{
    /** @var array $measures */
    protected $measures;

    /** @var PropertyAccessorInterface */
    protected $propertyAccessor;

    /**
     * Constructor
     *
     * @param PropertyAccessorInterface $propertyAccessor
     * @param array                     $measures
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor, $measures)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->measures = $measures['measures_config'];
    }

    /**
     * Validate metric type and default metric unit
     *
     * @param AttributeInterface|MetricInterface|ProductValueInterface $object
     * @param Constraint                                               $constraint
     */
    public function validate($object, Constraint $constraint)
    {
        if ($object instanceof AttributeInterface) {
            $familyProperty = 'metricFamily';
            $unitProperty   = 'defaultMetricUnit';
        } elseif ($object instanceof MetricInterface && null !== $object->getData()) {
            $familyProperty = 'family';
            $unitProperty   = 'unit';
        } elseif ($object instanceof ProductValueInterface && null !== $object->getMetric()
            && (null !== $object->getMetric()->getUnit() || null !== $object->getMetric()->getData())
        ) {
            $object = $object->getMetric();
            $familyProperty = 'family';
            $unitProperty   = 'unit';
        } else {
            return;
        }

        $family = $this->propertyAccessor->getValue($object, $familyProperty);
        $unit   = $this->propertyAccessor->getValue($object, $unitProperty);

        if (!array_key_exists($family, $this->measures)) {
            $this->context->buildViolation($constraint->familyMessage)
                ->atPath($familyProperty)
                ->addViolation();
        } elseif (!array_key_exists($unit, $this->measures[$family]['units'])) {
            $this->context->buildViolation($constraint->unitMessage)
                ->atPath($unitProperty)
                ->addViolation();
        }
    }
}
