<?php

namespace Oro\Bundle\WorkflowBundle\Tests\Unit\Twig;

use Oro\Bundle\WorkflowBundle\Twig\ClassNameExtension;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Twig\Stub\Entity;
use Oro\Bundle\WorkflowBundle\Tests\Unit\Twig\Stub\__CG__\EntityProxy;

class ClassNameExtensionTest extends \PHPUnit_Framework_TestCase
{
    const TEST_CLASS = '\stdClass';

    /**
     * @var ClassNameExtension
     */
    protected $twigExtension;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $metadataManager;

    protected function setUp()
    {
        $this->metadataManager = $this->getMockBuilder('Oro\Bundle\WorkflowBundle\Model\MetadataManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getEntityClass'))
            ->getMock();

        $this->twigExtension = new ClassNameExtension($this->metadataManager);
    }

    protected function tearDown()
    {
        unset($this->twigExtension);
        unset($this->metadataManager);
    }

    public function testGetFunctions()
    {
        $functions = $this->twigExtension->getFunctions();
        $this->assertCount(1, $functions);

        /** @var \Twig_SimpleFunction $function */
        $function = current($functions);
        $this->assertInstanceOf('\Twig_SimpleFunction', $function);
        $this->assertEquals('oro_class_name', $function->getName());
        $this->assertEquals(array($this->twigExtension, 'getClassName'), $function->getCallable());
    }

    /**
     * @param string $expectedClass
     * @param mixed $object
     * @dataProvider getClassNameDataProvider
     */
    public function testGetClassName($expectedClass, $object)
    {
        if (is_object($object)) {
            $this->metadataManager->expects($this->once())
                ->method('getEntityClass')
                ->with($object)
                ->will($this->returnValue(self::TEST_CLASS));
        } else {
            $this->metadataManager->expects($this->never())
                ->method('getEntityClass');
        }

        $this->assertEquals($expectedClass, $this->twigExtension->getClassName($object));
    }

    public function getClassNameDataProvider()
    {
        return array(
            'not an object' => array(
                'expectedClass' => null,
                'object'        => 'string',
            ),
            'object' => array(
                'expectedClass' => self::TEST_CLASS,
                'object'        => new \stdClass(),
            ),
        );
    }

    public function testGetName()
    {
        $this->assertEquals(ClassNameExtension::NAME, $this->twigExtension->getName());
    }
}
