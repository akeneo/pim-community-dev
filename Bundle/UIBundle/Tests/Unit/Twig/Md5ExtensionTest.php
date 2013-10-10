<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig;

use Oro\Bundle\UIBundle\Twig\Md5Extension;

class Md5ExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $serializer;

    /**
     * @var Md5Extension
     */
    private $extension;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->serializer = $this->getMockBuilder('JMS\Serializer\Serializer')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new Md5Extension($this->serializer);
    }

    public function testName()
    {
        $this->assertEquals('oro_md5', $this->extension->getName());
    }

    public function testMd5()
    {
        $this->assertEquals("3474851a3410906697ec77337df7aae4", $this->extension->md5("test_string"));
    }

    public function testObjectMd5WithString()
    {
        $this->assertEquals("3474851a3410906697ec77337df7aae4", $this->extension->objectMd5("test_string"));
    }

    public function testObjectMd5WithObject()
    {
        $object = new \stdClass();
        $this->serializer->expects($this->once())
            ->method('serialize')
            ->with($this->equalTo($object), $this->equalTo('json'))
            ->will($this->returnValue('some_string'));
        $this->assertEquals(md5('some_string'), $this->extension->objectMd5($object));
    }

    public function testSetFilters()
    {
        $this->assertArrayHasKey('md5', $this->extension->getFilters());
    }
}
