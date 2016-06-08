<?php

namespace Pim\Bundle\ImportExportBundle\Validator\Constraints;

use Pim\Bundle\ImportExportBundle\Validator\Constraints\UpdatedSinceDate;
use Pim\Bundle\ImportExportBundle\Validator\Constraints\UpdatedSinceNDays;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator for UpdateSinceStrategy constraint
 *
 * @author    Arnaud Langlade <arnaud.langlade@akeneo.com>
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UpdatedSinceStrategyValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof UpdatedSinceDate &&
            !$constraint instanceof UpdatedSinceNDays) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\UpdateSinceStrategy');
        }

        $strategy = $constraint->jobInstance->getRawConfiguration()['updated_since_strategy'];
        if (empty($value) && $constraint->strategy === $strategy) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
