<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Form\Subscriber\IgnoreMissingFieldDataSubscriber;
use Symfony\Component\Form\FormEvents;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas.quemener@gmail.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class IgnoreMissingFieldDataSubscriberTest extends \PHPUnit_Framework_TestCase
{
    protected $subscriber;

    public function setUp()
    {
        $this->subscriber = new IgnoreMissingFieldDataSubscriber();
    }

    public function testIsAnEventSubscriber()
    {
        $this->assertInstanceOf('Symfony\Component\EventDispatcher\EventSubscriberInterface', $this->subscriber);
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals(
            array(FormEvents::PRE_SUBMIT => 'preSubmit'),
            IgnoreMissingFieldDataSubscriber::getSubscribedEvents()
        );
    }

    public function testPreSubmit()
    {
        $form = $this->getFormMock(
            array(
                'firstname' => '**form**',
                'lastname'  => '**form**',
                'age'       => '**form**',
            )
        );
        $data = array(
            'firstname' => 'Romain',
            'lastname'  => 'Monceau',
        );
        $event = $this->getFormEventMock($form, $data);

        $form->expects($this->once())
            ->method('remove')
            ->with('age');

        $this->subscriber->preSubmit($event);
    }

    protected function getFormEventMock($form, $data)
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

    protected function getFormMock(array $children)
    {
        $form = $this
            ->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();

        $form->expects($this->any())
            ->method('all')
            ->will($this->returnValue($children));

        return $form;
    }
}
