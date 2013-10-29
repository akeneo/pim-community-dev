<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Form\Handler\CalendarEventHandler;
use Symfony\Component\HttpFoundation\Request;

class CalendarEventHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider supportedMethods
     * @param string $method
     */
    public function testProcess($method)
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $request = new Request();
        $om = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $request->setMethod($method);

        $obj  = new CalendarEvent();

        $form->expects($this->once())
            ->method('setData')
            ->with($this->identicalTo($obj));
        $form->expects($this->once())
            ->method('submit')
            ->with($this->identicalTo($request));
        $form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));
        $om->expects($this->once())
            ->method('persist')
            ->with($this->identicalTo($obj));
        $om->expects($this->once())
            ->method('flush');

        $handler = new CalendarEventHandler($form, $request, $om);
        $handler->process($obj);
    }

    public function supportedMethods()
    {
        return array(
            array('POST'),
            array('PUT')
        );
    }
}
