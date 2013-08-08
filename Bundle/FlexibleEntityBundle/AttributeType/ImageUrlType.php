<?php

namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

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
        return 'oro_flexibleentity_imageurl';
    }
}
