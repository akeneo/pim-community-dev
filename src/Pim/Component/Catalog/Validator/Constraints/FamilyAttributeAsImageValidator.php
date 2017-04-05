<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\FamilyInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Family attribute_as_image validator
 *
 * This validator will check that:
 * - the attribute defined as label is an attribute of the family
 * - the attribute type is "image"
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyAttributeAsImageValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($family, Constraint $constraint)
    {
        if (!$family instanceof FamilyInterface) {
            return;
        }

        if (null === $family->getAttributeAsImage()) {
            return;
        }

        if (!$this->doesAttributeAsImageBelongToFamily($family)) {
            $this->context
                ->buildViolation($constraint->messageAttribute)
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }

        if (!$this->isAttributeAsImageTypeValid($family)) {
            $this->context
                ->buildViolation($constraint->messageAttributeType)
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }

        if (!$this->isAttributeAsImageGlobal($family)) {
            $this->context
                ->buildViolation($constraint->messageAttributeGlobal)
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }
    }

    /**
     * @param FamilyInterface $family
     *
     * @return bool
     */
    protected function doesAttributeAsImageBelongToFamily(FamilyInterface $family)
    {
        return in_array($family->getAttributeAsImage()->getCode(), $family->getAttributeCodes());
    }

    /**
     * @param FamilyInterface $family
     *
     * @return bool
     */
    protected function isAttributeAsImageTypeValid(FamilyInterface $family)
    {
        return AttributeTypes::IMAGE === $family->getAttributeAsImage()->getType();
    }

    /**
     * @param FamilyInterface $family
     *
     * @return bool
     */
    protected function isAttributeAsImageGlobal(FamilyInterface $family)
    {
        return !$family->getAttributeAsImage()->isScopable() && !$family->getAttributeAsImage()->isLocalizable();
    }
}
