<?php

declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class OperationCountValidator extends ConstraintValidator
{
    /** @var int */
    private $min = 1;

    /** @var int */
    private $max;

    public function __construct(int $max)
    {
        $this->max = $max;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof OperationCount) {
            throw new UnexpectedTypeException($constraint, OperationCount::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_array($value) && !$value instanceof \Countable) {
            throw new UnexpectedValueException($value, 'array|\Countable');
        }

        $count = \count($value);

        if ($count > $this->max) {
            $this->context->buildViolation($constraint->maxMessage)
                ->setParameter('%limit%', $this->max)
                ->setInvalidValue($value)
                ->setPlural((int)$this->max)
                ->addViolation();

            return;
        }

        if ($count < $this->min) {
            $this->context->buildViolation($constraint->minMessage)
                ->setParameter('%limit%', $this->min)
                ->setInvalidValue($value)
                ->setPlural((int)$this->min)
                ->addViolation();
        }
    }
}
