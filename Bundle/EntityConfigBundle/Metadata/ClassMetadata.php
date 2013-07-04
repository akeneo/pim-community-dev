<?php

namespace Oro\Bundle\EntityConfigBundle\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;

class ClassMetadata extends BaseClassMetadata
{
    public $configurable = false;

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->configurable,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->configurable,
            $parentStr
            ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
