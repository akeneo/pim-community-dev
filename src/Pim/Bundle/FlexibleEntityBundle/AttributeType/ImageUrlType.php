<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

/**
 * Image attribute type
 */
class ImageUrlType extends FileUrlType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'pim_flexibleentity_imageurl';
    }
}
