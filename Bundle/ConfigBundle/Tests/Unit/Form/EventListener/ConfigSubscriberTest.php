<?php

namespace Oro\Bundle\ConfigBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\ConfigBundle\Form\EventListener\ConfigSubscriber;

class ConfigSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $configManager;

    /**
     * @var ConfigSubscriber
     */
    protected $subscriber;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    public function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\ConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->subscriber = new ConfigSubscriber($this->configManager);
    }

    /**
     * test preSubmit
     */
    public function testPreSubmit()
    {
        $data = array(
            'oro_user___level' => array(
                'use_parent_scope_value' => true,
            ),
        );

        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_user.level', true)
            ->will($this->returnValue(20));

        $this->event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        $data['oro_user___level']['value'] = 20;
        $this->event->expects($this->once())
            ->method('setData')
            ->with($data);

        $this->subscriber->preSubmit($this->event);
    }
}
