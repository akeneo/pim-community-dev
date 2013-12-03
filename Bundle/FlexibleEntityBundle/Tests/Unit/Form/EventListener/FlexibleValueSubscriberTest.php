<?php

namespace Oro\Bundle\FlexibleEntityBundle\Tests\Form\EventListener;

use Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Oro\Bundle\FlexibleEntityBundle\Form\EventListener\FlexibleValueSubscriber;
use Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

class FlexibleValueSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FormFactoryInterface
     */
    protected $formFactory;

    /**
     * @var AttributeTypeFactory
     */
    protected $attributeTypeFactory;

    /**
     * @var FlexibleManagerRegistry
     */
    protected $flexibleManagerRegistry;

    /**
     * @var FlexibleValueSubscriber
     */
    protected $subscriber;

    protected function setUp()
    {
        $this->formFactory = $this->getMockBuilder('Symfony\Component\Form\FormFactoryInterface')
            ->getMock();
        $this->attributeTypeFactory
            = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory')
                ->disableOriginalConstructor()
                ->getMock();
        $this->flexibleManagerRegistry
            = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry')
                ->disableOriginalConstructor()
                ->getMock();
        $this->subscriber = new FlexibleValueSubscriber(
            $this->formFactory,
            $this->attributeTypeFactory,
            $this->flexibleManagerRegistry
        );
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(FormEvents::PRE_SET_DATA => 'preSetData'), $this->subscriber->getSubscribedEvents());
    }

    public function testPreSetDataNoData()
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData');
        $event->expects($this->once())
            ->method('getForm');
        $this->assertEmpty($this->subscriber->preSetData($event));
    }

    public function testPreSetDataNoFlexibleAttrs()
    {
        $this->assertAttrbiuteValueFormInit('Acme\TestBundle\Entity\Test');
    }

    public function testPreSetDataWithFlexibleAttribute()
    {
        $dataClass = 'Oro\Bundle\FlexibleEntityBundle\Model\AbstractFlexible';

        $attributeEntity = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Model\FlexibleInterface')
            ->getMock();

        $flexibleManager = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
            ->disableOriginalConstructor()
            ->getMock();
        $flexibleManager->expects($this->once())
            ->method('createFlexible')
            ->will($this->returnValue($attributeEntity));

        $this->flexibleManagerRegistry->expects($this->once())
            ->method('getManager')
            ->with($dataClass)
            ->will($this->returnValue($flexibleManager));

        $this->assertAttrbiuteValueFormInit($dataClass, $attributeEntity);

    }

    protected function assertAttrbiuteValueFormInit($dataClass, $valueFormData = null)
    {
        $attributeTypeName = 'test_attribute';
        $attribute = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Model\AbstractAttribute')
            ->getMock();
        $attribute->expects($this->once())
            ->method('getAttributeType')
            ->will($this->returnValue($attributeTypeName));
        $data = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface')
            ->getMock();
        $data->expects($this->once())
            ->method('getAttribute')
            ->will($this->returnValue($attribute));

        $config = $this->getMockBuilder('Symfony\Component\Form\FormConfigInterface')
            ->getMock();
        $config->expects($this->once())
            ->method('getDataClass')
            ->will($this->returnValue($dataClass));

        $valueForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $valueForm->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($config));

        if ($valueFormData) {
            $valueForm->expects($this->once())
                ->method('setData')
                ->with($valueFormData);
        }

        $attributeType = $this->getMockBuilder('Oro\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeInterface')
            ->getMock();
        $attributeType->expects($this->once())
            ->method('buildValueFormType')
            ->will($this->returnValue($valueForm));

        $this->attributeTypeFactory->expects($this->once())
            ->method('get')
            ->with($attributeTypeName)
            ->will($this->returnValue($attributeType));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->once())
            ->method('add')
            ->with($valueForm);

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));
        $event->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($form));

        $this->subscriber->preSetData($event);
    }
}
