<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

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
        if (!$constraint instanceof IsIdentifierUsableAsGridFilter) {
            throw new UnexpectedTypeException($constraint, IsIdentifierUsableAsGridFilter::class);
        }

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
