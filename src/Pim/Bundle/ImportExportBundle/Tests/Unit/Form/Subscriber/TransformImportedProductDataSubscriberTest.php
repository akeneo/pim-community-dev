<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Form\Subscriber;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\ImportExportBundle\Form\Subscriber\TransformImportedProductDataSubscriber;

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
        $this->productEnabledConverter    = $this->getConverterMock('ProductEnabledConverter');
        $this->productValueConverter      = $this->getConverterMock('ProductValueConverter');
        $this->productFamilyConverter     = $this->getConverterMock('ProductFamilyConverter');
        $this->productCategoriesConverter = $this->getConverterMock('ProductCategoriesConverter');

        $this->subscriber = new TransformImportedProductDataSubscriber(
            $this->productEnabledConverter,
            $this->productValueConverter,
            $this->productFamilyConverter,
            $this->productCategoriesConverter
        );

        $this->form = $this->getFormMock();
    }

    public function testInstanceOfEventSubscriber()
    {
        $this->assertInstanceOf(
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            $this->subscriber
        );
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
            ->will($this->returnValue(array('enabled' => '1')));

        $this->productValueConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(array('values' => array('sku' => 'sku-001'))));

        $this->productFamilyConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(array('family' => 4)));

        $this->productCategoriesConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(array('categories' => array(1, 2, 3))));

        $this->subscriber->preSubmit($event);

        $data = $event->getData();

        $this->assertArrayHasKey('enabled', $data);
        $this->assertArrayHasKey('values', $data);
        $this->assertArrayHasKey('family', $data);
        $this->assertArrayHasKey('categories', $data);

        $this->assertEquals('1', $data['enabled']);
        $this->assertEquals(array('sku' => 'sku-001'), $data['values']);
        $this->assertEquals(4, $data['family']);
        $this->assertEquals(array(1, 2, 3), $data['categories']);
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
