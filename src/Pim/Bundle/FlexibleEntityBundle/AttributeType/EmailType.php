<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Mail attribute type
 */
class EmailType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_email';
    }
}
