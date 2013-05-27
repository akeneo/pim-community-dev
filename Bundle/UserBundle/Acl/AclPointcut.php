<?php

namespace Oro\Bundle\UserBundle\Acl;

use JMS\AopBundle\Aop\PointcutInterface;

class AclPointcut implements PointcutInterface
{

    /**
     * Check class for ACL
     *
     * @param  \ReflectionClass $class
     * @return bool
     */
    public function matchesClass(\ReflectionClass $class)
    {
        $className = $class->getName();

        if (substr($className, -10, 10) == 'Controller' &&
            strpos($className, 'ExceptionController') === false
        ) {
            return true;
        }

        return false;
    }

    /**
     * Check method for Acl
     *
     * @param  \ReflectionMethod $method
     * @return bool
     */
    public function matchesMethod(\ReflectionMethod $method)
    {
        if (substr($method->getName(), -6, 6) == 'Action') {
            return true;
        }

        return false;
    }
}
