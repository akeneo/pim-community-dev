<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\RangeValidator as BaseRangeValidator;
use Symfony\Component\Validator\Constraint;
use Pim\Bundle\FlexibleEntityBundle\Entity\Metric;
use Pim\Bundle\CatalogBundle\Model\ProductPrice;

/**
 * Validator for range constraint
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeValidator extends BaseRangeValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value instanceof \DateTime) {
            if ($constraint->min && $value < $constraint->min) {
                $this->context->addViolation(
                    $constraint->minDateMessage,
                    array(
                        '{{ limit }}' => $constraint->min->format('Y-m-d')
                    )
                );
            }

            if ($constraint->max && $value > $constraint->max) {
                $this->context->addViolation(
                    $constraint->maxDateMessage,
                    array(
                        '{{ limit }}' => $constraint->max->format('Y-m-d')
                    )
                );
            }

            return;
        }

        if ($value instanceof Metric || $value instanceof ProductPrice) {
            $this->validateData($value->getData(), $constraint);
        } else {
            parent::validate($value, $constraint);
        }
    }

    /**
     * Validate the data property of a Metric or ProductPrice value
     * and add the violation to the 'data' property path
     *
     * @param mixed      $value
     * @param Constraint $constraint
     */
    protected function validateData($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        $propertyPath = 'data';

        if (!is_numeric($value)) {
            $this->context->addViolationAt($propertyPath, $constraint->invalidMessage, array(
                '{{ value }}' => $value,
            ));

            return;
        }

        if (null !== $constraint->max && $value > $constraint->max) {
            $this->context->addViolationAt($propertyPath, $constraint->maxMessage, array(
                '{{ value }}' => $value,
                '{{ limit }}' => $constraint->max,
            ));

            return;
        }

        if (null !== $constraint->min && $value < $constraint->min) {
            $this->context->addViolationAt($propertyPath, $constraint->minMessage, array(
                '{{ value }}' => $value,
                '{{ limit }}' => $constraint->min,
            ));
        }
    }
}
