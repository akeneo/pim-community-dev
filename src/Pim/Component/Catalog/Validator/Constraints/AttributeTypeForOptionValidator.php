<?php

namespace Pim\Component\Catalog\Validator\Constraints;

use Pim\Component\Catalog\AttributeTypes;
use Pim\Component\Catalog\Model\AttributeOptionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Validator for attribute used for an option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeForOptionValidator extends ConstraintValidator
{
    /** @var array */
    protected $supportedAttributeTypes;

    /**
     * AttributeTypeForOptionValidator constructor.
     * @param array $supportedAttributeTypes
     */
    public function __construct(array $supportedAttributeTypes)
    {
        $this->supportedAttributeTypes = $supportedAttributeTypes;
    }

    /**
     * @param object                            $attributeOption
     * @param AttributeTypeForOption|Constraint $constraint
     */
    public function validate($attributeOption, Constraint $constraint)
    {
        /** @var AttributeOptionInterface */
        if ($attributeOption instanceof AttributeOptionInterface) {
            $attribute = $attributeOption->getAttribute();
            if (null !== $attribute && !in_array($attribute->getType(), $this->supportedAttributeTypes)) {
                $this->addInvalidAttributeViolation($constraint, $attributeOption, $this->supportedAttributeTypes);
            }
        }
    }

    /**
     * @param AttributeTypeForOption   $constraint
     * @param AttributeOptionInterface $option
     * @param string[]                 $authorizedTypes
     */
    protected function addInvalidAttributeViolation(
        AttributeTypeForOption $constraint,
        AttributeOptionInterface $option,
        array $authorizedTypes
    ) {
        $this->context
            ->buildViolation(
                $constraint->invalidAttributeMessage,
                [
                    '%attribute%'       => $option->getAttribute()->getCode(),
                    '%attribute_types%' => implode('", "', $authorizedTypes),
                ]
            )
            ->atPath($constraint->propertyPath)
            ->addViolation();
    }
}
