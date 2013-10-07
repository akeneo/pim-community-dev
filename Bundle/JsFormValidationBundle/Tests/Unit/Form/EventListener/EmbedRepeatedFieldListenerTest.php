<?php

namespace Oro\Bundle\JsFormValidationBundle\Tests\Unit\Form\EvenListener;

use APY\JsFormValidationBundle\Generator\FieldsConstraints;
use APY\JsFormValidationBundle\Generator\PostProcessEvent;

use Oro\Bundle\JsFormValidationBundle\Form\EventListener\EmbedRepeatedFieldListener;
use Symfony\Component\Form\FormView;

class EmbedRepeatedFieldListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $aclManager;

    /** @var  ChangePasswordSubscriber */
    protected $subscriber;

    public function setUp()
    {
        $this->subscriber = new EmbedRepeatedFieldListener();
    }

    /**
     * Test onJsfvPostProcess
     */
    public function testOnJsfvPostProcess()
    {
        $targetFormView = $childFormView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $childFormView->children = array($targetFormView);

        $eventMock = $this->getMockBuilder('APY\JsFormValidationBundle\Generator\PostProcessEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $formView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $formView->children = array($childFormView);

        $eventMock->expects($this->once())
            ->method('getFormView')
            ->will($this->returnValue($formView));

        $eventMock->expects($this->once())
            ->method('getFieldsConstraints')
            ->will($this->returnValue(array()));

        $this->subscriber->onJsfvPostProcess($eventMock);
    }

    /**
     * Test that listener added constraint
     */
    public function testListener()
    {
        $formView = new FormView();
        $childFormView = new FormView();

        $targetFormView = new FormView;
        $targetFormView->vars['type'] = 'repeated';
        $targetFormView->vars['id'] = 'repeated_field_id';
        $targetFormView->vars['value'] = array('first' => '', 'second' => '');

        $childFormView->children = array(
            $targetFormView
        );
        $formView->children = array(
            $childFormView
        );
        $fieldConstraints = new FieldsConstraints();

        $event = new PostProcessEvent($formView, $fieldConstraints);
        $this->subscriber->onJsfvPostProcess($event);

        $newFieldConstraints = $event->getFieldsConstraints();

        $this->assertCount(1, $newFieldConstraints->constraints);
        $this->assertArrayHasKey('repeated_field_id_second', $newFieldConstraints->constraints);
    }
}
