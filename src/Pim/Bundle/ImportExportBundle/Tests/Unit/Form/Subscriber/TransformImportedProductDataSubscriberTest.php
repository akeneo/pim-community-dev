<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Form\Subscriber;

use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Pim\Bundle\ImportExportBundle\Form\Subscriber\TransformImportedProductDataSubscriber;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class TransformImportedProductDataSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->productValueConverter      = $this->getConverterMock('ProductValueConverter');

        $this->subscriber = new TransformImportedProductDataSubscriber(
            $this->productValueConverter
        );

        $this->form = $this->getFormMock();
    }

    /**
     * Test related method
     */
    public function testInstanceOfEventSubscriber()
    {
        $this->assertInstanceOf(
            'Symfony\Component\EventDispatcher\EventSubscriberInterface',
            $this->subscriber
        );
    }

    /**
     * Test related method
     */
    public function testSubscribedEvent()
    {
        $this->assertEquals(
            array(FormEvents::PRE_SUBMIT => 'preSubmit'),
            TransformImportedProductDataSubscriber::getSubscribedEvents()
        );
    }

    /**
     * Test related method
     */
    public function testTransformImportedData()
    {
        $event = new FormEvent(
            $this->form,
            array(
                'enabled'    => true,
                'family'     => 4,
                'categories' => array(1, 2, 3),
                'groups'     => array(4,5,6),
                'bogus'      => false
            )
        );
        $this->productValueConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->returnValue(array('values' => array('sku' => 'sku-001'))));

        $this->subscriber->preSubmit($event);

        $data = $event->getData();
        $this->assertArrayHasKey('enabled', $data);
        $this->assertArrayHasKey('values', $data);
        $this->assertArrayHasKey('family', $data);
        $this->assertArrayHasKey('categories', $data);
        $this->assertArrayHasKey('groups', $data);
        $this->assertArrayNotHasKey('bogus', $data);

        $this->assertEquals('1', $data['enabled']);
        $this->assertEquals(array('sku' => 'sku-001'), $data['values']);
        $this->assertEquals(4, $data['family']);
        $this->assertEquals(array(1, 2, 3), $data['categories']);
        $this->assertEquals(array(4, 5, 6), $data['groups']);
    }

    /**
     * @expectedException Oro\Bundle\BatchBundle\Item\InvalidItemException
     * @expectedExceptionMessage So wrong!
     */
    public function testConvertInvalidArgumentIntoInvalidItemException()
    {
        $event = new FormEvent($this->form, array());

        $this->productValueConverter
            ->expects($this->any())
            ->method('convert')
            ->will($this->throwException(new \InvalidArgumentException('So wrong!')));

        $this->subscriber->preSubmit($event);
    }

    /**
     * @return \Symfony\Component\Form\Form
     */
    protected function getFormMock()
    {
        $form = $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $form->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($this->getFormConfigMock()));

        return $form;
    }

    /**
     * @param string $class
     *
     * @return \Pim\Bundle\ImportExportBundle\Converter\mixed
     */
    protected function getConverterMock($class)
    {
        return $this
            ->getMockBuilder(sprintf('Pim\\Bundle\\ImportExportBundle\\Converter\\%s', $class))
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getFormConfigMock()
    {
        $formConfig = $this->getMock('Symfony\Component\Form\FormConfigInterface');
        $formConfig->expects($this->any())
            ->method('getOptions')
            ->will(
                $this->returnValue(
                    array(
                        'family_column'     => 'family',
                        'categories_column' => 'categories',
                        'groups_column'     => 'groups',
                    )
                )
            );

        return $formConfig;
    }
}
