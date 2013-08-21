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
        if (!$value instanceof ProductValueInterface) {
            return;
        }

        if ($value === null || $value->getData() === null) {
            $this->context->addViolation($constraint->messageNotNull);

            return;
        }

        $data = $value->getData();
        if ($data === '' || empty($data)) {
            $this->context->addViolation($constraint->messageNotBlank);
        }
    }
}
