<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\MetricInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ProductPriceInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\RangeValidator as BaseRangeValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Webmozart\Assert\Assert;

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

        if (null === $value) {
            return;
        }

        switch (true) {
            case $value instanceof \DateTimeInterface:
                $this->validateDateTime($value, $constraint);
                break;

            case $value instanceof MetricInterface || $value instanceof ProductPriceInterface:
                $this->validateData($value->getData(), $constraint);
                break;

            case !is_numeric($value) && !$value instanceof \DateTimeInterface:
                $this->context->buildViolation(
                    $constraint->invalidMessage,
                    [
                        '{{ attribute }}' => $constraint->attributeCode,
                        '{{ value }}' => $value,
                    ]
                )
                    ->setCode(Range::INVALID_CHARACTERS_ERROR)
                    ->addViolation();
                break;

            default:
                // it allows to have a proper message when the value is superior to the technical maximum value allowed by PHP
                // we don't put it by default, as otherwise the message is quite weird for the user (between 0 and 9.22E18)
                if ((null === $constraint->max && is_numeric($value) && $value > PHP_INT_MAX) || PHP_INT_MAX < $constraint->max) {
                    $constraint->max = PHP_INT_MAX;
                }

                parent::validate($value, $constraint);
                break;
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

        if (null !== $constraint->min && $value < $constraint->min) {
            $this->context->buildViolation($constraint->minMessage, [
                '{{ value }}' => $value,
                '{{ limit }}' => $constraint->min,
            ])
                ->atPath('data')
                ->setCode(Range::TOO_LOW_ERROR)
                ->addViolation();
        }

        if (null !== $constraint->max && $value > $constraint->max) {
            $this->context->buildViolation($constraint->maxMessage, [
                '{{ value }}' => $value,
                '{{ limit }}' => $constraint->max,
            ])
                ->atPath('data')
                ->setCode(Range::TOO_HIGH_ERROR)
                ->addViolation();
        }
    }

    private function validateDateTime(\DateTimeInterface $dateTime, Constraint $constraint): void
    {
        Assert::isInstanceOf($constraint, Range::class);
        if ($constraint->min && $dateTime < $constraint->min) {
            $this->context->buildViolation(
                $constraint->minDateMessage,
                [
                    '{{ limit }}' => $constraint->min->format('Y-m-d'),
                    '{{ attribute_code }}' => $constraint->attributeCode,
                ]
            )
                ->setCode(Range::TOO_LOW_ERROR)
                ->addViolation();
        }

        if ($constraint->max && $dateTime > $constraint->max) {
            $this->context->buildViolation(
                $constraint->maxDateMessage,
                [
                    '{{ limit }}' => $constraint->max->format('Y-m-d'),
                    '{{ attribute_code }}' => $constraint->attributeCode,
                ]
            )
                ->setCode(Range::TOO_HIGH_ERROR)
                ->addViolation();
        }
    }
}
