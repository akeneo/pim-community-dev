<?php

namespace Pim\Bundle\ProductBundle\Validator\ConstraintGuesser;

use Symfony\Component\Validator\Constraints as Assert;
use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class RangeGuesser implements ConstraintGuesserInterface
{
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return in_array($attribute->getBackendType(), array(
            AbstractAttributeType::BACKEND_TYPE_INTEGER,
            AbstractAttributeType::BACKEND_TYPE_METRIC,
            AbstractAttributeType::BACKEND_TYPE_PRICE,
        ));
    }

    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();
        $min = $attribute->getNumberMin();
        $max = $attribute->getNumberMax();
        if ($min || $max) {
            if (false === $attribute->getNegativeAllowed()) {
                $min = 0;
            }
            $constraints[] = new Assert\Range(array(
                'min' => $min,
                'max' => $max,
            ));
        }

        return $constraints;
    }
}

