<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\FamilyInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Family attribute_as_label validator
 *
 * This validator will check that:
 * - the attribute defined as label is an attribute of the family
 * - the attribute type is "text" or "identifier"
 *
 * @author    Pierre Allard <pierre.allard@akeneo.com>
 * @copyright 2017 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FamilyAttributeAsLabelValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($family, Constraint $constraint)
    {
        if (!$constraint instanceof FamilyAttributeAsLabel) {
            throw new UnexpectedTypeException($constraint, FamilyAttributeAsLabel::class);
        }

        if (!$family instanceof FamilyInterface) {
            return;
        }

        if (!$this->doesAttributeAsLabelBelongToFamily($family)) {
            $this->context
                ->buildViolation($constraint->messageAttribute)
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }

        if (!$this->isAttributeAsLabelTypeValid($family)) {
            $this->context
                ->buildViolation($constraint->messageAttributeType)
                ->atPath($constraint->propertyPath)
                ->addViolation();
        }
    }

    /**
     * @param FamilyInterface $family
     *
     * @return bool
     */
    protected function doesAttributeAsLabelBelongToFamily(FamilyInterface $family)
    {
        $attributeAsLabel = $family->getAttributeAsLabel();
        if (null === $attributeAsLabel) {
            return false;
        }

        return in_array($attributeAsLabel->getCode(), $family->getAttributeCodes());
    }

    /**
     * @param FamilyInterface $family
     *
     * @return bool
     */
    protected function isAttributeAsLabelTypeValid(FamilyInterface $family)
    {
        $attributeAsLabel = $family->getAttributeAsLabel();
        if (null === $attributeAsLabel) {
            return false;
        }

        return in_array($family->getAttributeAsLabel()->getType(), [
            AttributeTypes::IDENTIFIER,
            AttributeTypes::TEXT,
        ]);
    }
}
