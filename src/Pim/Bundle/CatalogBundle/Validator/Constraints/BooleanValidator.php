<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\Model\ProductValueInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * BooleanValidator
 *
 * @author    JM Leroux <jean-marie.leroux@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class BooleanValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value) {
            return;
        }

        $code = '';
        $checkedValue = $value;

        if ($value instanceof ProductValueInterface) {
            $code = $value->getAttribute()->getCode();
            $getter = sprintf('get%s', ucfirst($value->getAttribute()->getBackendType()));
            $checkedValue = $value->$getter();
        }

        if (null === $checkedValue) {
            return;
        }

        if (!is_bool($checkedValue)
            && '0' !== $checkedValue
            && '1' !== $checkedValue
            && 0 !== $checkedValue
            && 1 !== $checkedValue
        ) {
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
