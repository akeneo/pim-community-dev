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
class TypeGuesser implements ConstraintGuesserInterface
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

        if (!$attribute->getDecimalsAllowed()) {
            $constraints[] = new Assert\Type(array(
                'type' => 'int',
            ));
        }

        return $constraints;
    }
}

