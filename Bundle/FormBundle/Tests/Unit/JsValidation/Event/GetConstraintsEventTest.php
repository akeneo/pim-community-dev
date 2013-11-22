<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\JsValidation\Event;

use Oro\Bundle\FormBundle\JsValidation\Event\GetConstraintsEvent;

class GetConstraintsEventTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $formView;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $constraints;

    /**
     * @var GetConstraintsEvent
     */
    protected $event;

    protected function setUp()
    {
        $this->formView = $this->getMock('Symfony\Component\Form\FormView');
        $this->constraints = $this->getMock('Doctrine\Common\Collections\Collection');
        $this->event = new GetConstraintsEvent($this->formView, $this->constraints);
    }

    public function testGetFormView()
    {
        $this->assertEquals($this->formView, $this->event->getFormView());
    }

    public function testGetConstraints()
    {
        $this->assertEquals($this->constraints, $this->event->getConstraints());
    }

    public function testAddConstraint()
    {
        $constraint = $this->getMock('Symfony\Component\Validator\Constraint');
        $this->constraints->expects($this->once())->method('add')->with($constraint);
        $this->event->addConstraint($constraint);
    }
}
