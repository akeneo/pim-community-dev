<?php

namespace Oro\Bundle\FlexibleEntityBundle\Form\Validator;

use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Symfony\Component\Validator\Constraints;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

class AttributeConstraintGuesser implements ConstraintGuesserInterface
{
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return true;
    }

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
        }

        return $constraints;
    }
}
