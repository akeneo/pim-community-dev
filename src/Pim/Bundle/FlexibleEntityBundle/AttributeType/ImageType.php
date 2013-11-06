<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

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
        return 'pim_flexibleentity_image';
    }
}
