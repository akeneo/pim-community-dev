<?php
declare(strict_types=1);

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UnitCountValidator extends ConstraintValidator
{
    private int $max;

    public function __construct(int $max)
    {
        $this->max = $max;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof UnitCount) {
            throw new UnexpectedTypeException($constraint, UnitCount::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_array($value) && !$value instanceof \Countable) {
            throw new UnexpectedValueException($value, 'array|\Countable');
        }

        $count = is_countable($value) ? \count($value) : 0;

        if ($count > $this->max) {
            $this->context->buildViolation(UnitCount::MAX_MESSAGE)
                ->setParameter('%limit%', (string)$this->max)
                ->setInvalidValue($value)
                ->setPlural((int)$this->max)
                ->addViolation();
        }

        if ($count < MeasurementFamily::MIN_UNIT_COUNT) {
            $this->context->buildViolation(UnitCount::MIN_MESSAGE)
                ->setParameter('%limit%', (string)MeasurementFamily::MIN_UNIT_COUNT)
                ->setInvalidValue($value)
                ->addViolation();
        }
    }
}
