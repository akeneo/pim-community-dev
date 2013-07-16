<?php

namespace Oro\Bundle\EntityBundle\Metadata;

use Metadata\ClassMetadata;

class AuditEntityMetadata extends ClassMetadata
{
    public $auditable = false;

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->auditable,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->auditable,
            $parentStr
            ) = unserialize($str);

        parent::unserialize($parentStr);
    }
}

