<?php
declare(strict_types=1);

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * @author    Willy Mesnage <willy.mesnage@akeneo.com>
 * @copyright 2020 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class NotDecimalValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof NotDecimal) {
            throw new UnexpectedTypeException($constraint, NotDecimal::class);
        }

        if (null === $value) {
            return;
        }
        if (is_numeric($value) && floor((int) $value) != $value) {
            $violation = $this->context->buildViolation($constraint->message);
            $violation->addViolation();
        }
    }
}
