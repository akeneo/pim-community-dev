<?php

namespace Pim\Bundle\ProductBundle\Validator;

use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ConstraintGuesser implements ConstraintGuesserInterface
{
    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();

        if ($attribute->getRequired()) {
            $constraints[] = new Assert\NotBlank;
        }

        switch ($attribute->getBackendType()) {
            case AbstractAttributeType::BACKEND_TYPE_INTEGER:
                if ($attribute->getNumberMin() || $attribute->getNumberMax()) {
                    $constraints[] = new Assert\Range(array(
                        'min' => $attribute->getNumberMin(),
                        'max' => $attribute->getNumberMax(),
                    ));
                }
                if (!$attribute->getNegativeAllowed()) {
                    $constraints[] = new Assert\Min(array(
                        'limit' => 0
                    ));
                }
                if (!$attribute->getNegativeAllowed()) {
                    $constraints[] = new Assert\Type(array(
                        'type' => 'int'
                    ));
                }
                break;
        }

        return $constraints;
    }
}

