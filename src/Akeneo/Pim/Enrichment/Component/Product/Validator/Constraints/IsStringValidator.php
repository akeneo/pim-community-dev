<?php

namespace Akeneo\Pim\Enrichment\Component\Product\Validator\Constraints;

use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Constraint
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsStringValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof IsString) {
            throw new UnexpectedTypeException($constraint, IsString::class);
        }

        if (null === $value) {
            return;
        }

        $code = '';
        $checkedValue = $value;

        if ($value instanceof ValueInterface) {
            $code = $value->getAttributeCode();
            $checkedValue = $value->getData();
        }

        if (null === $checkedValue) {
            return;
        }

        if (!is_string($checkedValue)) {
            $this->context->buildViolation(
                $constraint->message,
                [
                    '%attribute%' => $code,
                    '%givenType%' => gettype($checkedValue),
                ]
            )->addViolation();
        }
    }
}
