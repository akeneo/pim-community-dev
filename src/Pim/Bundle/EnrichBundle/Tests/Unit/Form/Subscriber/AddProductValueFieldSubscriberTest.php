<?php

namespace Pim\Bundle\EnrichBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeFactory;
use Pim\Bundle\EnrichBundle\Form\Subscriber\AddProductValueFieldSubscriber;
use Pim\Bundle\CatalogBundle\Manager\ProductManager;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class AddProductValueFieldSubscriberTest extends \PHPUnit_Framework_TestCase
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
     * @var ProductManager
     */
    protected $productManager;

    /**
     * @var AddProductValueFieldSubscriber
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
            ->getMockBuilder('Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->productManager = $this
            ->getMockBuilder('Pim\Bundle\CatalogBundle\Manager\ProductManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->subscriber = new AddProductValueFieldSubscriber(
            $this->formFactory,
            $this->attributeTypeFactory,
            $this->productManager
        );
    }

    /**
     * Test related method
     */
    public function testGetSubscribedEvents()
    {
        $this->assertEquals(array(FormEvents::PRE_SET_DATA => 'preSetData'), $this->subscriber->getSubscribedEvents());
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
        $this->assertProductValueFormInit('Acme\TestBundle\Entity\Test');
    }

    /**
     * Test related method
     */
    public function testPreSetDataWithProduct()
    {
        $dataClass = 'Pim\Bundle\CatalogBundle\Model\ProductInterface';

        $productEntity = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\Product')
            ->getMock();

        $this->assertProductValueFormInit($dataClass, $productEntity);

    }

    /**
     * @param string $dataClass
     * @param object $valueFormData
     */
    protected function assertProductValueFormInit($dataClass, $valueFormData = null)
    {
        $attributeTypeName = 'test_attribute';
        $attribute = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\AbstractAttribute')
            ->getMock();
        $attribute->expects($this->once())
            ->method('getAttributeType')
            ->will($this->returnValue($attributeTypeName));
        $data = $this->getMockBuilder('Pim\Bundle\CatalogBundle\Model\ProductValueInterface')
            ->getMock();
        $data->expects($this->once())
            ->method('getAttribute', 'getData', 'setData', '__toString')
            ->will($this->returnValue($attribute));

        $config = $this->getMockBuilder('Symfony\Component\Form\FormConfigInterface')
            ->getMock();

        $valueForm = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $attributeType = $this->getMockBuilder('Pim\Bundle\CatalogBundle\AttributeType\AttributeTypeInterface')
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
