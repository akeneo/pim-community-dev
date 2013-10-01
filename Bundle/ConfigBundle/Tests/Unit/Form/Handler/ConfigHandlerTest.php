<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\Form\Handler;

use Oro\Bundle\ConfigBundle\Form\Handler\ConfigHandler;

class ConfigHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $form;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $request;

    /**
     * @var ConfigHandler
     */
    protected $handler;

    public function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->form = $this->getMock('Symfony\Component\Form\Test\FormInterface');
        $this->request = $request = $this->getMock('Symfony\Component\HttpFoundation\Request');

        $this->handler = new ConfigHandler($this->configManager);
    }

    /**
     * test process method
     */
    public function testProcess()
    {
        $settings = array();

        $this->configManager->expects($this->once())
            ->method('getSettingsByForm')
            ->with($this->isInstanceOf('Symfony\Component\Form\Test\FormInterface'))
            ->will($this->returnValue($settings));

        $this->form->expects($this->once())
            ->method('setData')
            ->with($settings);

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->equalTo($this->request));

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $this->form->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($settings));

        $this->configManager->expects($this->once())
            ->method('save');

        $this->assertTrue($this->handler->process($this->form, $this->request));
    }

    public function testBadRequest()
    {
        $settings = array();

        $this->configManager->expects($this->once())
            ->method('getSettingsByForm')
            ->with($this->isInstanceOf('Symfony\Component\Form\Test\FormInterface'))
            ->will($this->returnValue($settings));

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

        $this->configManager->expects($this->never())
            ->method('save');

        $this->assertFalse($this->handler->process($this->form, $this->request));
    }

    public function testFormNotValid()
    {
        $settings = array();

        $this->configManager->expects($this->once())
            ->method('getSettingsByForm')
            ->with($this->isInstanceOf('Symfony\Component\Form\Test\FormInterface'))
            ->will($this->returnValue($settings));

        $this->request->expects($this->once())
            ->method('getMethod')
            ->will($this->returnValue('POST'));

        $this->form->expects($this->once())
            ->method('setData')
            ->with($settings);

        $this->form->expects($this->once())
            ->method('submit')
            ->with($this->equalTo($this->request));

        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $this->configManager->expects($this->never())
            ->method('save');

        $this->assertFalse($this->handler->process($this->form, $this->request));
    }
}
