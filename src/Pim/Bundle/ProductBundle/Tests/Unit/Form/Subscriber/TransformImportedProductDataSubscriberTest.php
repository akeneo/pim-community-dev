<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\ProductBundle\Form\Subscriber\TransformImportedProductDataSubscriber;
use Symfony\Component\Form\FormEvents;

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
        $this->subscriber = new TransformImportedProductDataSubscriber();
    }

    public function testSubscribedEvent()
    {
        $this->assertEquals(
            array(FormEvents::PRE_SUBMIT => 'preSubmit'),
            TransformImportedProductDataSubscriber::getSubscribedEvents()
        );
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testOnlySupportProductTypeForm()
    {
        $form = $this->getFormMock();
        $event = $this->getEventMock($form);

        $this->subscriber->preSubmit($event);
    }

    private function getEventMock($form, $data = array())
    {
        $event = $this
            ->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($form));

        $event->expects($this->any())
            ->method('getData')
            ->will($this->returnValue($data));

        return $event;
    }

    private function getFormMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
