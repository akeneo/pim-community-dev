<?php

namespace Pim\Bundle\CatalogBundle\Validator\Constraints;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypes;
use Pim\Bundle\CatalogBundle\Model\GroupInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for variant group axis constraint
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class VariantGroupAxisValidator extends ConstraintValidator
{
    /**
     * Axis must be provided for new variant group
     *
     * @param object     $variantGroup
     * @param Constraint $constraint
     */
    public function validate($variantGroup, Constraint $constraint)
    {
        /* @var GroupInterface */
        if ($variantGroup instanceof GroupInterface) {
            $isNew = null === $variantGroup->getId();
            $isVariantGroup = $variantGroup->getType()->isVariant();
            $hasAxis = count($variantGroup->getAxisAttributes()) > 0;
            if ($isNew && $isVariantGroup && !$hasAxis) {
                $this->addExpectedAxisViolation($constraint, $variantGroup->getCode());
            } elseif ($isVariantGroup && $hasAxis) {
                $this->validateAttributeAxis($constraint, $variantGroup);
            } elseif (!$isVariantGroup && $hasAxis) {
                $this->addUnexpectedAxisViolation($constraint, $variantGroup->getCode());
            }
        }
    }

    /**
     * @param VariantGroupAxis $constraint
     * @param GroupInterface   $variantGroup
     */
    protected function validateAttributeAxis(VariantGroupAxis $constraint, GroupInterface $variantGroup)
    {
        $allowedTypes = [AttributeTypes::OPTION_SIMPLE_SELECT, AttributeTypes::REFERENCE_DATA_SIMPLE_SELECT];
        foreach ($variantGroup->getAxisAttributes() as $attribute) {
            if (!in_array($attribute->getAttributeType(), $allowedTypes)) {
                $this->addInvalidAxisViolation($constraint, $variantGroup->getCode(), $attribute->getCode());
            }
        }
    }

    /**
     * @param VariantGroupAxis $constraint
     * @param string           $groupCode
     */
    protected function addExpectedAxisViolation(VariantGroupAxis $constraint, $groupCode)
    {
        $this->context->buildViolation(
            $constraint->expectedAxisMessage,
            [
                '%variant group%' => $groupCode
            ]
        )->addViolation();
    }

    /**
     * @param VariantGroupAxis $constraint
     * @param string           $groupCode
     */
    protected function addUnexpectedAxisViolation(VariantGroupAxis $constraint, $groupCode)
    {
        $this->context->buildViolation(
            $constraint->unexpectedAxisMessage,
            [
                '%group%' => $groupCode
            ]
        )->addViolation();
    }

    /**
     * @param VariantGroupAxis $constraint
     * @param string           $groupCode
     * @param string           $attributeCode
     */
    protected function addInvalidAxisViolation(VariantGroupAxis $constraint, $groupCode, $attributeCode)
    {
        $this->context->buildViolation(
            $constraint->invalidAxisMessage,
            [
                '%group%'     => $groupCode,
                '%attribute%' => $attributeCode,
            ]
        )->addViolation();
    }
}
