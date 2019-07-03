<?php

namespace Akeneo\Pim\Structure\Component\Validator\Constraints;

use Akeneo\Pim\Structure\Component\AttributeTypes;
use Akeneo\Pim\Structure\Component\Model\AttributeOptionInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validator for attribute used for an option
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2015 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AttributeTypeForOptionValidator extends ConstraintValidator
{
    /** @var string[] */
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
        if (!$constraint instanceof AttributeTypeForOption) {
            throw new UnexpectedTypeException($constraint, AttributeTypeForOption::class);
        }

        $authorizedTypes = $this->supportedAttributeTypes;

        /** @var AttributeOptionInterface */
        if ($attributeOption instanceof AttributeOptionInterface) {
            $attribute = $attributeOption->getAttribute();
            if (null !== $attribute && !in_array($attribute->getType(), $authorizedTypes)) {
                $this->addInvalidAttributeViolation($constraint, $attributeOption, $authorizedTypes);
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
