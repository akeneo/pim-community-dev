<?php

namespace Oro\Bundle\EntityConfigBundle\Metadata;

use Metadata\PropertyMetadata;

class FieldMetadata extends PropertyMetadata
{
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
    public function serialize()
    {
        return serialize(
            array(
                $this->defaultValues,
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
            $this->defaultValues,
            $this->mode,
            $parentStr
            ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
