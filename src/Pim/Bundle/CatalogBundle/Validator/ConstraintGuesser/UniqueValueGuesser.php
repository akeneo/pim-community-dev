<?php

namespace Pim\Bundle\CatalogBundle\Validator\ConstraintGuesser;

use Oro\Bundle\FlexibleEntityBundle\Form\Validator\ConstraintGuesserInterface;
use Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute;
use Oro\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;
use Pim\Bundle\CatalogBundle\Validator\Constraints\UniqueValue;

/**
 * Guesser
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class UniqueValueGuesser implements ConstraintGuesserInterface
{
    /**
     * {@inheritdoc}
     */
    public function supportAttribute(AbstractAttribute $attribute)
    {
        $availableBackendTypes = array(
            AbstractAttributeType::BACKEND_TYPE_VARCHAR,
            AbstractAttributeType::BACKEND_TYPE_DATE,
            AbstractAttributeType::BACKEND_TYPE_DATETIME,
            AbstractAttributeType::BACKEND_TYPE_DECIMAL,
            AbstractAttributeType::BACKEND_TYPE_INTEGER
        );

        return in_array($attribute->getBackendType(), $availableBackendTypes);
    }

    /**
     * {@inheritdoc}
     */
    public function guessConstraints(AbstractAttribute $attribute)
    {
        $constraints = array();

        if ($attribute->getUnique()) {
            $constraints[] = new UniqueValue();
        }

        return $constraints;
    }
}
