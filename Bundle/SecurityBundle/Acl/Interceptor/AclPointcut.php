<?php

namespace Oro\Bundle\SecurityBundle\Acl\Interceptor;

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
        $className = $method->getDeclaringClass()->getName();

        $result = $this->annotationProvider->isProtectedMethod($className, $method->getName());
        // it is supposed that a method is protected is it has no own ACL annotation but the declaring class has
        if (!$result) {
            $result = $this->annotationProvider->hasAnnotation($className);
        }

        return $result;
    }
}
