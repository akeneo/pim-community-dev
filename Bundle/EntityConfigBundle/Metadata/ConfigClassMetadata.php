<?php

namespace Oro\Bundle\EntityConfigBundle\Metadata;

use Metadata\MergeableClassMetadata;
use Metadata\MergeableInterface;

class ConfigClassMetadata extends MergeableClassMetadata
{
    /**
     * @var bool
     */
    public $configurable = false;
    public $routeName;

    /**
     * @var string
     */
    public $viewMode;

    /**
     * {@inheritdoc}
     */
    public function merge(MergeableInterface $object)
    {
        parent::merge($object);

        if ($object instanceof ConfigClassMetadata) {
            $this->configurable = $object->configurable;
            $this->routeName    = $object->routeName;
            $this->viewMode     = $object->viewMode;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->configurable,
            $this->routeName,
            $this->viewMode,
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
            $this->routeName,
            $this->viewMode,
            $parentStr
            ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
