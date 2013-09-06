<?php

namespace Oro\Bundle\EntityBundle\ORM;

use Symfony\Component\Security\Core\Util\ClassUtils;

/**
 * This class allows to get the real class name of an entity
 */
class EntityClassAccessor
{
    /**
     * marker for extend entities and proxy classes
     */
    const MARKER_EXTEND = 'Extend\\Entity\\Proxy\\';

    /**
     * Gets the real class name for the given entity or the given class name that could be a proxy
     *
     * @param object|string $objectOrClassName
     * @return string
     */
    public function getClass($objectOrClassName)
    {
        $class = is_object($objectOrClassName) ? get_class($objectOrClassName) : $objectOrClassName;
        if (false !== $pos = strrpos($class, self::MARKER_EXTEND)) {
            $classParts = explode('\\', $class);

            return implode('\\', array_slice($classParts, 3));
        }

        return ClassUtils::getRealClass($objectOrClassName);
    }
}
