<?php

namespace Oro\Bundle\EntityExtendBundle\Mapping;

use Doctrine\ORM\Mapping\ClassMetadataFactory;

class ExtendClassMetadataFactory extends ClassMetadataFactory
{
    public function setMetadataFor($className, $class)
    {
        $this->getCacheDriver()->save(
            $className . $this->cacheSalt, $className, null
        );

        parent::setMetadataFor($className, $class);
    }
}
