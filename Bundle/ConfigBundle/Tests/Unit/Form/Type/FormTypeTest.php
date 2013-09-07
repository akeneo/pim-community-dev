<?php

namespace ConfigBundle\Tests\Unit\Form\Type;

use Oro\Bundle\ConfigBundle\Form\Type\FormType;

class FormTypeTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $subscriber;

    /** @var FormType */
    protected $form;

    public function setUp()
    {
        $this->subscriber = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Form\EventListener\ConfigSubscriber')
            ->disableOriginalConstructor()->getMock();
        $this->form       = new FormType($this->subscriber);
    }

    public function tearDown()
    {
        unset($this->subscriber);
        unset($this->form);
    }

    public function testBuildForm()
    {
        $builder = $this->getMockBuilder('Symfony\Component\Form\Test\FormBuilderInterface')
            ->disableOriginalConstructor()->getMock();

        $builder->expects($this->once())->method('addEventSubscriber')->with($this->equalTo($this->subscriber));
        $this->form->buildForm($builder, array());
    }

    public function testGetName()
    {
        $this->assertEquals('oro_config_form_type', $this->form->getName());
    }
}
