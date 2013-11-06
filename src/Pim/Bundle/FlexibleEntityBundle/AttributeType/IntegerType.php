<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Integer attribute type
 */
class IntegerType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_integer';
    }
}
