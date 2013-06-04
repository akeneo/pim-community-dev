<?php

namespace Oro\Bundle\DataAuditBundle\Metadata;

use Metadata\PropertyMetadata as BasePropertyMetadata;

class PropertyMetadata extends BasePropertyMetadata
{
    public $isCollection = false;

    public $method = false;

    public function serialize()
    {
        return serialize(array(
            $this->isCollection,
            $this->method,
            parent::serialize(),
        ));
    }

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

