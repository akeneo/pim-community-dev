<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit;

class ReflectionUtil
{
    /**
     * @param mixed $obj
     * @param mixed $val
     */
    public static function setId($obj, $val)
    {
        $class = new \ReflectionClass($obj);
        $prop  = $class->getProperty('id');
        $prop->setAccessible(true);

        $prop->setValue($obj, $val);
    }

    /**
     * @param mixed $obj
     * @param mixed $val
     */
    public static function setCreatedAt($obj, $val)
    {
        $class = new \ReflectionClass($obj);
        $prop  = $class->getProperty('createdAt');
        $prop->setAccessible(true);

        $prop->setValue($obj, $val);
    }

    /**
     * @param mixed  $obj
     * @param string $propName
     * @param mixed  $val
     */
    public static function setPrivateProperty($obj, $propName, $val)
    {
        $class = new \ReflectionClass($obj);
        $prop  = $class->getProperty($propName);
        $prop->setAccessible(true);

        $prop->setValue($obj, $val);
    }

    /**
     * @param mixed $obj
     * @param string $methodName
     * @param array $args
     * @return mixed
     */
    public static function callProtectedMethod($obj, $methodName, array $args)
    {
        $class = new \ReflectionClass($obj);
        $method = $class->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($obj, $args);
    }
}
