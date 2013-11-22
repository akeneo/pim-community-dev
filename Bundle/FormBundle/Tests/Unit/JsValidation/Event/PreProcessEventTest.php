<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\JsValidation\Event;

use Oro\Bundle\FormBundle\JsValidation\Event\PreProcessEvent;

class PreProcessEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formView;

    /**
     * @var PreProcessEvent
     */
    protected $event;

    protected function setUp()
    {
        $this->formView = $this->getMock('Symfony\Component\Form\FormView');
        $this->event = new PreProcessEvent($this->formView);
    }

    public function testGetFormView()
    {
        $this->assertEquals($this->formView, $this->event->getFormView());
    }
}
