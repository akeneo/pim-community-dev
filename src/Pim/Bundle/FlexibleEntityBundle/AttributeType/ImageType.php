<?php

namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

/**
 * Image attribute type
 */
class ImageType extends FileType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_image';
    }
}
