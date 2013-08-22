<?php

namespace Oro\Bundle\SecurityBundle\Acl\Domain;

use Symfony\Component\Security\Core\Util\ClassUtils;

/**
 * This class allows to get the real class name of a domain object
 */
class ObjectClassAccessor
{
    /**
     * Gets the real class name for the given domain object or the given class name that could be a proxy
     *
     * @param object|string $domainObjectOrClassName
     * @return string
     */
    public function getClass($domainObjectOrClassName)
    {
        return ClassUtils::getRealClass($domainObjectOrClassName);
    }
}
