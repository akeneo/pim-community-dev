<?php

namespace Pim\Bundle\ProductBundle\Validator\Constraints;

use Pim\Bundle\ProductBundle\Model\ProductValueInterface;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validate if a product value is not null and not empty
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ProductValueNotBlankValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if ($value === null) {
            $this->context->addViolation($constraint->messageNotNull);
        }

        if (!$value instanceof ProductValueInterface) {
            return;
        }

        $data = $value->getData();
        if ($data === null) {
            $this->context->addViolation($constraint->messageNotNull);

            return;
        }
        if ($data === ''
            || (is_array($data) && count($data) === 0)) {
            $this->context->addViolation($constraint->messageNotBlank);
        }
    }
}
