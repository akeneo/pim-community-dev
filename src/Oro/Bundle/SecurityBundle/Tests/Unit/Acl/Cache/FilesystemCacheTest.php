<?php

namespace Oro\Bundle\SecurityBundle\Tests\Unit\Acl\Cache;

use Oro\Bundle\SecurityBundle\Acl\Cache\FilesystemCache;

class FilesystemCacheTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getFilenameProvider
     */
    public function testGetFilename($id, $expectedFileName)
    {
        $cache = $this->getMockBuilder('Oro\Bundle\SecurityBundle\Acl\Cache\FilesystemCache')
            ->disableOriginalConstructor()
            ->setMethods(array('fetch'))
            ->getMock();

        self::setProtectedProperty($cache, 'directory', 'dir');
        self::setProtectedProperty($cache, 'extension', '.ext');

        $this->assertEquals(
            $expectedFileName,
            self::callProtectedMethod($cache, 'getFilename', array($id))
        );
    }

    public static function getFilenameProvider()
    {
        return array(
            array('test', 'dir' . DIRECTORY_SEPARATOR . 'test.ext'),
            array('test\\\\//::""**??<<>>||file', 'dir' . DIRECTORY_SEPARATOR . 'testfile.ext'),
        );
    }

    /**
     * @param mixed  $obj
     * @param string $propName
     * @param mixed  $val
     */
    public static function setProtectedProperty($obj, $propName, $val)
    {
        $class = new \ReflectionClass($obj);
        $prop = $class->getProperty($propName);
        $prop->setAccessible(true);

        $prop->setValue($obj, $val);
    }

    /**
     * @param  mixed  $obj
     * @param  string $methodName
     * @param  array  $args
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
