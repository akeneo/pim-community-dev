<?php

namespace Oro\Bundle\UserBundle\Acl;

use JMS\AopBundle\Aop\PointcutInterface;
use Oro\Bundle\SecurityBundle\Metadata\AclAnnotationProvider;

class AclPointcut implements PointcutInterface
{
    /**
     * @var AclAnnotationProvider
     */
    protected $annotationProvider;

    /**
     * Constructor
     *
     * @param AclAnnotationProvider $annotationProvider
     */
    public function __construct(AclAnnotationProvider $annotationProvider)
    {
        $this->annotationProvider = $annotationProvider;
    }

    /**
     * Determines whether the given class is protected by ACL security policy.
     *
     * @param  \ReflectionClass $class
     * @return bool
     */
    public function matchesClass(\ReflectionClass $class)
    {
        return $this->annotationProvider->isProtectedClass($class->getName());
    }

    /**
     * Determines whether the given method is protected by ACL security policy.
     *
     * @param  \ReflectionMethod $method
     * @return bool
     */
    public function matchesMethod(\ReflectionMethod $method)
    {
        return $this->annotationProvider->isProtectedMethod(
            $method->getDeclaringClass()->getName(),
            $method->getName()
        );
    }
}
