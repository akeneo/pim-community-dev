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
use Symfony\Component\Validator\Constraints\CountValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class OperationCountValidator extends CountValidator
{
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

        $constraint->min = 1;
        $constraint->max = $this->max;

        return parent::validate($value, $constraint);
    }
}
