<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\ProductAttributeInterface;
use Pim\Bundle\FlexibleEntityBundle\Entity\Attribute;
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
    /**
     * @var array $measures
     */
    protected $measures;

    /**
     * @var PropertyAccessorInterface
     */
    protected $propertyAccessor;

    /**
     * Constructor
     *
     * @param array $measures
     */
    public function __construct(PropertyAccessorInterface $propertyAccessor, $measures)
    {
        $this->propertyAccessor = $propertyAccessor;
        $this->measures = $measures['measures_config'];
    }

    /**
     * Validate metric type and default metric unit
     *
     * @param ProductAttributeInterface $entity
     * @param Constraint                $constraint
     */
    public function validate($entity, Constraint $constraint)
    {
        if ($entity instanceof Attribute) {
            $familyProperty = 'metricFamily';
            $unitProperty   = 'defaultMetricUnit';
        } else {
            $familyProperty = 'family';
            $unitProperty   = 'unit';
        }

        $family = $this->propertyAccessor->getValue($entity, $familyProperty);
        $unit   = $this->propertyAccessor->getValue($entity, $unitProperty);

        if (!array_key_exists($family, $this->measures)) {
            $this->context->addViolationAt($familyProperty, $constraint->familyMessage);
        } elseif (!array_key_exists($unit, $this->measures[$family]['units'])) {
            $this->context->addViolationAt($unitProperty, $constraint->unitMessage);
        }
    }
}
