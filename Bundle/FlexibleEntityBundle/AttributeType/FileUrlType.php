<?php

namespace Oro\Bundle\FlexibleEntityBundle\AttributeType;

/**
 * File attribute type
 */
class FileUrlType extends UrlType
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_flexibleentity_fileurl';
    }
}
