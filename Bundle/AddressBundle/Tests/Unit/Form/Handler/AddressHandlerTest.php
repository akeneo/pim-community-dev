<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\Handler;

use Oro\Bundle\AddressBundle\Form\Handler\AddressHandler;

class AddressHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $form;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $request;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $om;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $address;

    /**
     * @var AddressHandler
     */
    private $handler;

    public function setUp()
    {
        $this->form = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $this->request = $request = $this->getMock('Symfony\Component\HttpFoundation\Request');
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->address = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Address')
            ->disableOriginalConstructor()
            ->getMock();

        $this->handler = new AddressHandler($this->form, $this->request, $this->om);
    }

    public function testGoodRequest()
    {
        $this->form->expects($this->once())
            ->method('setData');

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $this->form->expects($this->once())
            ->method('submit');
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue('true'));

        $this->om->expects($this->once())
            ->method('persist')
            ->with($this->equalTo($this->address));
        $this->om->expects($this->once())
            ->method('flush');

        $this->assertTrue($this->handler->process($this->address));
    }

    public function testBadRequest()
    {
        $this->form->expects($this->once())
            ->method('setData');

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('GET'));

        $this->form->expects($this->never())
            ->method('submit');
        $this->form->expects($this->never())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->om->expects($this->never())
            ->method('persist');
        $this->om->expects($this->never())
            ->method('flush');

        $this->assertFalse($this->handler->process($this->address));
    }

    public function testNotValidForm()
    {
        $this->form->expects($this->once())
            ->method('setData');

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $this->form->expects($this->once())
            ->method('submit');
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->om->expects($this->never())
            ->method('persist');
        $this->om->expects($this->never())
            ->method('flush');

        $this->assertFalse($this->handler->process($this->address));
    }
}
