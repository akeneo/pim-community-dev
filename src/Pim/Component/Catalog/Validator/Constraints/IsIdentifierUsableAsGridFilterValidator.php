<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @author    Damien Carcel (damien.carcel@akeneo.com)
 * @copyright 2016 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IsIdentifierUsableAsGridFilterValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($attribute, Constraint $constraint)
    {
        if ($attribute instanceof AttributeInterface &&
            AttributeTypes::IDENTIFIER === $attribute->getType() &&
            !$attribute->isUseableAsGridFilter()
        ) {
            $this->context
                ->buildViolation($constraint->message)
                ->setParameter('%code%', $attribute->getCode())
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }
    }
}
