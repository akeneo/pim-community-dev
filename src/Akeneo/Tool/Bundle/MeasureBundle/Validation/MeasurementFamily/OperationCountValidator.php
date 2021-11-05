<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class OperationCountValidator extends ConstraintValidator
{
    private int $min = 1;
    private int $max;

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

        $count = is_countable($value) ? \count($value) : 0;

        if ($count > $this->max) {
            $this->context->buildViolation($constraint->maxMessage)
                ->setParameter('%limit%', (string)$this->max)
                ->setInvalidValue($value)
                ->setPlural((int)$this->max)
                ->addViolation();

            return;
        }

        if ($count < $this->min) {
            $this->context->buildViolation($constraint->minMessage)
                ->setParameter('%limit%', (string)$this->min)
                ->setInvalidValue($value)
                ->setPlural((int)$this->min)
                ->addViolation();
        }
    }
}
