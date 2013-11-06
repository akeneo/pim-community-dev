<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AbstractAttributeType;

/**
 * Text attribute type
 */
class TextType extends AbstractAttributeType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_text';
    }
}
