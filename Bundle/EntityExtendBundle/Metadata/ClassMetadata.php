<?php

namespace Oro\Bundle\EntityExtendBundle\Metadata;

use Metadata\ClassMetadata as BaseClassMetadata;

class ClassMetadata extends BaseClassMetadata
{
    public $isExtend = false;

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->isExtend,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->isExtend,
            $parentStr
            ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
