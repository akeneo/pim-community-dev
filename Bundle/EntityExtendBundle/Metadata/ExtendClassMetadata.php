<?php

namespace Oro\Bundle\EntityExtendBundle\Metadata;

use Metadata\MergeableClassMetadata;
use Metadata\MergeableInterface;

class ExtendClassMetadata extends MergeableClassMetadata
{
    /**
     * @var bool
     */
    public $isExtend = false;

    /**
     * {@inheritdoc}
     */
    public function merge(MergeableInterface $object)
    {
        parent::merge($object);

        if ($object instanceof ExtendClassMetadata) {
            $this->isExtend = $object->isExtend;
        }
    }


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
