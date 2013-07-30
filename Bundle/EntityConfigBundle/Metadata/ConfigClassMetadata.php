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

    /**
     * @var string
     */
    public $routeName;

    /**
     * @var string
     */
    public $viewMode;

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

        if ($object instanceof ConfigClassMetadata) {
            $this->configurable  = $object->configurable;
            $this->defaultValues = $object->defaultValues;
            $this->routeName     = $object->routeName;
            $this->viewMode      = $object->viewMode;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->configurable,
            $this->defaultValues,
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
            $this->defaultValues,
            $this->routeName,
            $this->viewMode,
            $parentStr
            ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
