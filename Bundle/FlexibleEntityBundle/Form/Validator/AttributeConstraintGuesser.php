<?php

namespace Oro\Bundle\FlexibleEntityBundle\Form\Validator;

use Symfony\Component\Validator\Constraints;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Attribute constraint guesser
 */
class AttributeConstraintGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();

        if ($attribute->getRequired()) {
            $constraints[] = new Constraints\NotBlank();
        }

        switch ($attribute->getBackendType()) {
            case AbstractAttributeType::BACKEND_TYPE_DATE:
                $constraints[] = new Constraints\Date();
                break;
            case AbstractAttributeType::BACKEND_TYPE_DATETIME:
                $constraints[] = new Constraints\DateTime();
                break;
        }

        switch ($attribute->getAttributeType()) {
            case 'oro_flexibleentity_email':
                $constraints[] = new Constraints\Email();
                break;
            case 'oro_flexibleentity_integer_unsigned':
                $constraints[] = new Constraints\Range(array('min' => 0));
                break;
        }

        return $constraints;
    }
}
