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
class MinGuesser implements ConstraintGuesserInterface
{
    public function supportAttribute(AbstractAttribute $attribute)
    {
        return in_array($attribute->getBackendType(), array(
            AbstractAttributeType::BACKEND_TYPE_INTEGER,
            AbstractAttributeType::BACKEND_TYPE_METRIC,
        ));
    }

    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();

        if (!$attribute->getNegativeAllowed()) {
            $constraints[] = new Assert\Min(array(
                'limit' => 0
            ));
        }

        return $constraints;
    }
}

