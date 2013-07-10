<?php

namespace Oro\Bundle\EntityExtendBundle\Mapping;

use Doctrine\ORM\Mapping\ClassMetadataFactory;

class ExtendClassMetadataFactory extends ClassMetadataFactory
{
    public function getMetadataFor($className)
    {
        if (is_subclass_of($className, 'Oro\Bundle\EntityExtendBundle\Entity\ExtendProxyInterface')) {
            $className = get_parent_class($className);
        }

        return parent::getMetadataFor($className);
    }
}
