<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\EntityConfigBundle\Tests\Unit\ConfigManagerTest;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\EntityConfigBundle\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Form\EventListener\ConfigSubscriber;

class ConfigSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ConfigManager
     */
    protected $configManager;

    /**
     * @var ConfigSubscriber
     */
    protected $subscriber;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager->expects($this->any())->method('hasConfig')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('flush')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('getProviders')->will($this->returnValue(array()));

        $this->subscriber = new ConfigSubscriber($this->configManager);
    }

    public function testPostBind()
    {
        $form       = $this->getMock('Symfony\Component\Form\Tests\FormInterface');
        $formConfig = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $formConfig->expects($this->any())->method('getOptions')->will(
            $this->returnValue(array('class_name' => ConfigManagerTest::DEMO_ENTITY))
        );

        $form->expects($this->any())->method('getConfig')->will($this->returnValue($formConfig));

        $event = new FormEvent($form, array());

        $this->subscriber->postBind($event);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(FormEvents::POST_BIND => 'postBind'), ConfigSubscriber::getSubscribedEvents());
    }
}
