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
        $this->subscriber = new TransformImportedProductDataSubscriber();
        $this->form = $this->getFormMock();
    }

    public function testSubscribedEvent()
    {
        $this->assertEquals(
            array(FormEvents::PRE_SUBMIT => 'preSubmit'),
            TransformImportedProductDataSubscriber::getSubscribedEvents()
        );
    }

    public function testEnableImportedProduct()
    {
        $event = new FormEvent($this->form, array());

        $this->subscriber->setProductEnabled(true);

        $this->subscriber->preSubmit($event);

        $data = $event->getData();
        $this->assertArrayHasKey('enabled', $data);
        $this->assertTrue($data['enabled']);
    }

    public function testDisabledImportedProduct()
    {
        $event = new FormEvent($this->form, array());

        $this->subscriber->setProductEnabled(false);

        $this->subscriber->preSubmit($event);

        $data = $event->getData();
        $this->assertArrayHasKey('enabled', $data);
        $this->assertFalse($data['enabled']);
    }

    public function testIgnoreEnablingImportedProduct()
    {
        $event = new FormEvent($this->form, array());

        $this->subscriber->preSubmit($event);

        $data = $event->getData();
        $this->assertArrayNotHasKey('enabled', $data);
    }

    private function getFormMock()
    {
        return $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
