<?php

namespace Oro\Bundle\EntityBundle\ORM;

use Symfony\Component\Security\Core\Util\ClassUtils;

/**
 * This class allows to get the real class name of an entity
 */
class EntityClassAccessor
{
    /**
     * Gets the real class name for the given entity or the given class name that could be a proxy
     *
     * @param object|string $objectOrClassName
     * @return string
     */
    public function getClass($objectOrClassName)
    {
        return ClassUtils::getRealClass($objectOrClassName);
    }
}