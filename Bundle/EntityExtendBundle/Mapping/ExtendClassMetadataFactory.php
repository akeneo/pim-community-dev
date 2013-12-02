<?php

namespace Oro\Bundle\EntityExtendBundle\Mapping;

use Doctrine\ORM\Mapping\ClassMetadataFactory;

class ExtendClassMetadataFactory extends ClassMetadataFactory
{
    public function clearCache()
    {
        $this->getCacheDriver()->deleteAll();
    }

    public function setMetadataFor($className, $class)
    {
        $this->getCacheDriver()->save(
            $className . $this->cacheSalt,
            $class,
            null
        );

        parent::setMetadataFor($className, $class);
    }
}
