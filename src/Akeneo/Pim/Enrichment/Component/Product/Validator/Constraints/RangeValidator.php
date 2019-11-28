<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Akeneo\Pim\Structure\Component\AttributeTypes;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\RangeValidator as BaseRangeValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
        if (!$constraint instanceof Range) {
            throw new UnexpectedTypeException($constraint, Range::class);
        }

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
            // it allows to have a proper message when the value is superior to the technical maximum value allowed by PHP
            // we don't put it by default, as otherwise the message is quite weird for the user (between 0 and 9.22E18)
            if ((null === $constraint->max && is_numeric($value) && $value > PHP_INT_MAX) || PHP_INT_MAX < $constraint->max) {
                $constraint->max = PHP_INT_MAX;
            }

            parent::validate($value, $constraint);
        }
    }

    /**
     * Validate the data property of a Metric or ProductPrice value
     * and add the violation to the 'data' property path
     *
     * @param mixed $value
     * @param Range $constraint
     */
    protected function validateData($value, Range $constraint)
    {
        if (null === $value) {
            return;
        }

        $message = null;
        $params = [];

        if (null !== $constraint->max && $value > $constraint->max) {
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
