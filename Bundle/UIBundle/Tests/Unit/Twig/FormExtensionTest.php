<?php

namespace Oro\Bundle\UIBundle\Tests\Unit\Twig;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Oro\Bundle\UIBundle\Twig\FormExtension;
use Oro\Bundle\UIBundle\Event\Events;

class FormExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormExtension
     */
    private $extension;

    /**
     * @var EventDispatcher
     */
    protected $eventDispatcher;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->eventDispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();
        $this->extension = new FormExtension($this->eventDispatcher);
    }

    public function testName()
    {
        $this->assertEquals('oro_form_process', $this->extension->getName());
    }

    public function testProcess()
    {
        $env = $this->getMockBuilder('Twig_Environment')
            ->disableOriginalConstructor()
            ->getMock();
        $formView = $this->getMockBuilder('Symfony\Component\Form\FormView')
            ->disableOriginalConstructor()
            ->getMock();
        $this->eventDispatcher->expects($this->once())
             ->method('dispatch')
             ->with(
                 Events::BEFORE_UPDATE_FORM_RENDER,
                 $this->isInstanceOf('Oro\Bundle\UIBundle\Event\BeforeFormRenderEvent')
             );
        $formData = array("test");
        $this->assertEquals($formData, $this->extension->process($env, $formData, $formView));
    }

    public function testGetFunctions()
    {
        $this->assertArrayHasKey('oro_form_process', $this->extension->getFunctions());
    }
}
