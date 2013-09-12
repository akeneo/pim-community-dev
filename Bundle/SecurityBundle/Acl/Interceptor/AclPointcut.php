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
        // at the first check if a method has own ACL annotation
        $result = $this->annotationProvider->isProtectedMethod($method->class, $method->name);
        // if it hasn't and it is a public method
        if (!$result && $method->isPublic()) {
            // check if there is an ACL annotation for the class
            $result = $this->annotationProvider->hasAnnotation($method->class);
        }

        return $result;
    }
}
