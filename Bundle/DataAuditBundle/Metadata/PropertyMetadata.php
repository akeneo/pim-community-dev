<?php

namespace Oro\Bundle\DataAuditBundle\Metadata;

use Metadata\PropertyMetadata as BasePropertyMetadata;

class PropertyMetadata extends BasePropertyMetadata
{
    /**
     * @var bool
     */
    public $isCollection = false;

    /**
     * @var bool
     */
    public $method = false;

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->isCollection,
            $this->method,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->isCollection,
            $this->method,
            $parentStr
            ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}
