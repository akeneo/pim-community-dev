<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\MetricInterface;
use Pim\Bundle\CatalogBundle\Model\ProductPriceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\RangeValidator as BaseRangeValidator;

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
                $this->context->buildViolation(
                    $constraint->minDateMessage,
                    [
                        '{{ limit }}' => $constraint->min->format('Y-m-d')
                    ]
                )->addViolation();
            }

            if ($constraint->max && $value > $constraint->max) {
                $this->context->buildViolation(
                    $constraint->maxDateMessage,
                    [
                        '{{ limit }}' => $constraint->max->format('Y-m-d')
                    ]
                )->addViolation();
            }

            return;
        }

        if ($value instanceof MetricInterface || $value instanceof ProductPriceInterface) {
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

        $message = null;
        $params  = [];

        if (!is_numeric($value)) {
            $message = $constraint->invalidMessage;
            $params  = ['{{ value }}' => $value];
        } elseif (null !== $constraint->max && $value > $constraint->max) {
            $message = $constraint->maxMessage;
            $params = [
                '{{ value }}' => $value,
                '{{ limit }}' => $constraint->max,
            ];
        } elseif (null !== $constraint->min && $value < $constraint->min) {
            $message = $constraint->minMessage;
            $params = [
                '{{ value }}' => $value,
                '{{ limit }}' => $constraint->min,
            ];
        }

        if (null !== $message) {
            $this->context->buildViolation($message, $params)
                ->atPath('data')
                ->addViolation();
        }
    }
}
