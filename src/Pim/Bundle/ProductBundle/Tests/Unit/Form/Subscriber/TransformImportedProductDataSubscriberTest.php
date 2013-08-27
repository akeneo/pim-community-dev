<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\ProductBundle\Form\Subscriber\TransformImportedProductDataSubscriber;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformImportedProductDataSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->productEnabledConverter = $this->getConverterMock('ProductEnabledConverter');
        $this->productValueConverter = $this->getConverterMock('ProductValueConverter');
        $this->productFamilyConverter = $this->getConverterMock('ProductFamilyConverter');
        $this->subscriber = new TransformImportedProductDataSubscriber(
            $this->productEnabledConverter,
            $this->productValueConverter,
            $this->productFamilyConverter
        );
        $this->form = $this->getFormMock();
    }

    public function testSubscribedEvent()
    {
        $this->assertEquals(
            array(FormEvents::PRE_SUBMIT => 'preSubmit'),
            TransformImportedProductDataSubscriber::getSubscribedEvents()
        );
    }

    public function testTransformImportedData()
    {
        $event = new FormEvent($this->form, array());

        $this->productEnabledConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue('1'));

        $this->productValueConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(array('sku' => 'sku-001')));

        $this->productFamilyConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(4));

        $this->subscriber->preSubmit($event);

        $data = $event->getData();

        $this->assertArrayHasKey('enabled', $data);
        $this->assertArrayHasKey('values', $data);
        $this->assertArrayHasKey('family', $data);

        $this->assertEquals('1', $data['enabled']);
        $this->assertEquals(array('sku' => 'sku-001'), $data['values']);
        $this->assertEquals(4, $data['family']);
    }

    protected function getFormMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getConverterMock($class)
    {
        return $this
            ->getMockBuilder(sprintf('Pim\\Bundle\\ImportExportBundle\\Converter\\%s', $class))
            ->disableOriginalConstructor()
            ->getMock();
    }
}
