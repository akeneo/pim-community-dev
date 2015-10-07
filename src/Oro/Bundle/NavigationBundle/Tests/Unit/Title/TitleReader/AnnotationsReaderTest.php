<?php

namespace Oro\Bundle\NavigationBundle\Tests\Unit\Title\TitleReader;

use Oro\Bundle\NavigationBundle\Title\TitleReader\AnnotationsReader;

class AnnotationsReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $kernelMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $annotationReader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $testBundle;

    public function setUp()
    {
        if (!interface_exists('Doctrine\Common\Annotations\Reader')) {
            $this->markTestSkipped('Doctrine Common has to be installed for this test to run.');
        }

        $this->testBundle = $this->getMock(
            'Symfony\Bundle\FrameworkBundle\FrameworkBundle'
        );

        $this->kernelMock = $this->getMock(
            'Symfony\Component\HttpKernel\KernelInterface',
            array()
        );

        $this->annotationReader = $this->getMock(
            'Doctrine\Common\Annotations\AnnotationReader'
        );
    }

    public function testGetEmptyData()
    {

        $this->kernelMock->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array()));

        $reader = new AnnotationsReader($this->kernelMock, $this->annotationReader);
        $this->assertCount(0, $reader->getData(array()));
    }

    public function testGetData()
    {

        $this->kernelMock->expects($this->once())
            ->method('getBundles')
            ->will($this->returnValue(array($this->testBundle)));

        $routeMock = $this->getMock('Symfony\Component\Routing\Route', array(), array('/user/show/{id}'));

        $routeMock->expects($this->once())
            ->method('getDefault')
            ->with($this->equalTo('_controller'));

        $this->testBundle->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue(realpath(__DIR__)));

        $reader = new AnnotationsReader($this->kernelMock, $this->annotationReader);

        $this->assertInternalType('array', $reader->getData(array($routeMock)));
    }
}
