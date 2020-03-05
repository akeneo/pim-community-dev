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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

class OperationCountValidator extends ConstraintValidator
{
    private $min = 1;
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
                ->setParameter('{{ count }}', $count)
                ->setParameter('{{ limit }}', $this->max)
                ->setInvalidValue($value)
                ->setPlural((int)$this->max)
                ->addViolation();

            return;
        }

        if ($count < $this->min) {
            $this->context->buildViolation($constraint->minMessage)
                ->setParameter('{{ count }}', $count)
                ->setParameter('{{ limit }}', $this->min)
                ->setInvalidValue($value)
                ->setPlural((int)$this->min)
                ->addViolation();
        }
    }
}
