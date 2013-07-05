<?php

namespace Oro\Bundle\EntityConfigBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\EntityConfigBundle\Config\EntityConfig;
use Oro\Bundle\EntityConfigBundle\Config\FieldConfig;
use Oro\Bundle\EntityConfigBundle\DependencyInjection\EntityConfigContainer;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
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
        $entityConfig = new EntityConfig(ConfigManagerTest::DEMO_ENTITY, 'test');
        $entityConfig->addField(new FieldConfig(ConfigManagerTest::DEMO_ENTITY, 'testField', 'string', 'scope'));

        $this->configManager = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\ConfigManager')
            ->disableOriginalConstructor()
            ->getMock();

        $configProvider = new ConfigProvider($this->configManager, new EntityConfigContainer(array('scope' => 'test')));

        $this->configManager->expects($this->any())->method('getConfig')->will($this->returnValue($entityConfig));
        $this->configManager->expects($this->any())->method('hasConfig')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('flush')->will($this->returnValue(true));
        $this->configManager->expects($this->any())->method('getProviders')->will($this->returnValue(array($configProvider)));

        $this->subscriber = new ConfigSubscriber($this->configManager);
    }

    public function testPostBindEntity()
    {
        $form       = $this->getMock('Symfony\Component\Form\Tests\FormInterface');
        $formConfig = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $formConfig->expects($this->any())->method('getOptions')->will(
            $this->returnValue(array('class_name' => ConfigManagerTest::DEMO_ENTITY))
        );

        $form->expects($this->any())->method('getConfig')->will($this->returnValue($formConfig));

        $event = new FormEvent($form, array('test' => array('first' => 'data')));

        $this->subscriber->postBind($event);
    }

    public function testPostBindField()
    {
        $form       = $this->getMock('Symfony\Component\Form\Tests\FormInterface');
        $formConfig = $this->getMockBuilder('Symfony\Component\Form\FormBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $formConfig->expects($this->any())->method('getOptions')->will(
            $this->returnValue(array('class_name' => ConfigManagerTest::DEMO_ENTITY, 'field_name' => 'testField'))
        );

        $form->expects($this->any())->method('getConfig')->will($this->returnValue($formConfig));

        $event = new FormEvent($form, array('test' => array('testField' => 'data')));

        $this->subscriber->postBind($event);
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(FormEvents::POST_BIND => 'postBind'), ConfigSubscriber::getSubscribedEvents());
    }
}
