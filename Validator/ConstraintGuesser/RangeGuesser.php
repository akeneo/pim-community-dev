<?php

namespace Pim\Bundle\ProductBundle\Validator\ConstraintGuesser;

use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\ProductBundle\Validator\Constraints\Range;

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
            'prices',
            AbstractAttributeType::BACKEND_TYPE_DATE,
            AbstractAttributeType::BACKEND_TYPE_DATETIME,
        ));
    }

    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();

        if (in_array($attribute->getBackendType(), array(
            AbstractAttributeType::BACKEND_TYPE_DATE,
            AbstractAttributeType::BACKEND_TYPE_DATETIME,
        ))) {
            $min = $attribute->getDateMin();
            $max = $attribute->getDateMax();
        } else {
            $min = $attribute->getNumberMin();
            $max = $attribute->getNumberMax();
            if (false === $attribute->getNegativeAllowed()) {
                $min = 0;
            }
        }

        if (null !== $min || null !== $max) {
            $constraints[] = new Range(array(
                'min' => $min,
                'max' => $max,
            ));
        }

        return $constraints;
    }
}

