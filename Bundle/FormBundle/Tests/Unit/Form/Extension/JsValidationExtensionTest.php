<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\Extension;

use Symfony\Component\Form\FormView;

use Oro\Bundle\FormBundle\Form\Extension\JsValidationExtension;

class JsValidationExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $constraintsProvider;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventDispatcher;


    /**
     * @var JsValidationExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->constraintsProvider = $this->getMockBuilder('Oro\Bundle\FormBundle\JsValidation\ConstraintsProvider')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->extension = new JsValidationExtension($this->constraintsProvider, $this->eventDispatcher);
    }

    public function testBuildView()
    {
        $options = array();

        $view = new FormView();

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension->buildView($view, $form, $options);
    }
}
