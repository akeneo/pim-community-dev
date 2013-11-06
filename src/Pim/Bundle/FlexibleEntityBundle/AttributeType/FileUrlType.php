<?php

namespace Pim\Bundle\FlexibleEntityBundle\AttributeType;

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
        return 'pim_flexibleentity_fileurl';
    }
}
