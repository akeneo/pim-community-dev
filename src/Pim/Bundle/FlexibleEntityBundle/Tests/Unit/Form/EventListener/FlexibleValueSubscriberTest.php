<?php

namespace Pim\Bundle\FlexibleEntityBundle\Tests\Unit\Form\EventListener;

use Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\FlexibleEntityBundle\Form\EventListener\FlexibleValueSubscriber;
use Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
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

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->formFactory = $this->getMockBuilder('Symfony\Component\Form\FormFactoryInterface')
            ->getMock();
        $this->attributeTypeFactory = $this
            ->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->flexibleManagerRegistry = $this
            ->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManagerRegistry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriber = new FlexibleValueSubscriber(
            $this->formFactory,
            $this->attributeTypeFactory,
            $this->flexibleManagerRegistry
        );
    }

    /**
     * Test related method
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals([FormEvents::PRE_SET_DATA => 'preSetData'], $this->subscriber->getSubscribedEvents());
    }

    /**
     * Test related method
     */
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

    /**
     * Test related method
     */
    public function testPreSetDataNoFlexibleAttrs()
    {
        $this->assertAttrbiuteValueFormInit('Acme\TestBundle\Entity\Test');
    }

    /**
     * Test related method
     */
    public function testPreSetDataWithFlexibleAttribute()
    {
        $dataClass = 'Pim\Bundle\FlexibleEntityBundle\Model\AbstractFlexible';

        $attributeEntity = $this->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Model\FlexibleInterface')
            ->getMock();

        $flexibleManager = $this->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Manager\FlexibleManager')
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

    /**
     * @param string $dataClass
     * @param object $valueFormData
     */
    protected function assertAttrbiuteValueFormInit($dataClass, $valueFormData = null)
    {
        $attributeTypeName = 'test_attribute';
        $attribute = $this->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Model\AbstractAttribute')
            ->getMock();
        $attribute->expects($this->once())
            ->method('getAttributeType')
            ->will($this->returnValue($attributeTypeName));
        $data = $this->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\Model\FlexibleValueInterface')
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

        $attributeType = $this->getMockBuilder('Pim\Bundle\FlexibleEntityBundle\AttributeType\AttributeTypeInterface')
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
