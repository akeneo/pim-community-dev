<?php
declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2020 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Tool\Bundle\MeasureBundle\Validation\MeasurementFamily;

use Akeneo\Tool\Bundle\MeasureBundle\Model\MeasurementFamily;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class UnitCountValidator extends ConstraintValidator
{
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
        if (!$constraint instanceof UnitCount) {
            throw new UnexpectedTypeException($constraint, UnitCount::class);
        }

        if (null === $value) {
            return;
        }

        if (!\is_array($value) && !$value instanceof \Countable) {
            throw new UnexpectedValueException($value, 'array|\Countable');
        }

        $count = \count($value);

        if ($count > $this->max) {
            $this->context->buildViolation(UnitCount::MAX_MESSAGE)
                ->setParameter('%limit%', $this->max)
                ->setInvalidValue($value)
                ->setPlural((int)$this->max)
                ->addViolation();
        }

        if ($count < MeasurementFamily::MIN_UNIT_COUNT) {
            $this->context->buildViolation(UnitCount::MIN_MESSAGE)
                ->setParameter('%limit%', MeasurementFamily::MIN_UNIT_COUNT)
                ->setInvalidValue($value)
                ->addViolation();
        }
    }
}
