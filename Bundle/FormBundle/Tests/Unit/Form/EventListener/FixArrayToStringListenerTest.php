<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Form\Type;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\Tests\FormInterface;

use Oro\Bundle\FormBundle\Form\EventListener\FixArrayToStringListener;

class FixArrayToStringListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider preBindDataProvider
     *
     * @param string $delimiter
     * @param mixed  $data
     * @param mixed  $expectedData
     */
    public function testPreBind($delimiter, $data, $expectedData)
    {
        $event = new FormEvent($this->getMock('Symfony\Component\Form\Test\FormInterface'), $data);
        $listener = new FixArrayToStringListener($delimiter);
        $listener->preBind($event);
        $this->assertEquals($expectedData, $event->getData());
    }

    /**
     * @return array
     */
    public function preBindDataProvider()
    {
        return array(
            'skip' => array(
                ',',
                '1,2,3,4',
                '1,2,3,4',
            ),
            'convert array to string' => array(
                ',',
                array(1, 2, 3, 4),
                '1,2,3,4',
            )
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(FormEvents::PRE_BIND => 'preBind'), FixArrayToStringListener::getSubscribedEvents());
    }
}
