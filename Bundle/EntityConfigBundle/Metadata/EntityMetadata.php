<?php

namespace Oro\Bundle\EntityConfigBundle\Metadata;

use Metadata\MergeableClassMetadata;
use Metadata\MergeableInterface;

class EntityMetadata extends MergeableClassMetadata
{
    /**
     * @var bool
     */
    public $configurable = false;

    /**
     * @var string
     */
    public $routeName;

    /**
     * @var string
     */
    public $mode;

    /**
     * @var array
     */
    public $defaultValues;

    /**
     * {@inheritdoc}
     */
    public function merge(MergeableInterface $object)
    {
        parent::merge($object);

        if ($object instanceof EntityMetadata) {
            $this->configurable  = $object->configurable;
            $this->defaultValues = $object->defaultValues;
            $this->routeName     = $object->routeName;
            $this->mode          = $object->mode;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            array(
                $this->configurable,
                $this->defaultValues,
                $this->routeName,
                $this->mode,
                parent::serialize(),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->configurable,
            $this->defaultValues,
            $this->routeName,
            $this->mode,
            $parentStr
            ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
