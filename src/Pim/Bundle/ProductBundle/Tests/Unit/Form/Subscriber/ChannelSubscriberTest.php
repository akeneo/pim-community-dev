<?php

namespace Pim\Bundle\ProductBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\ProductBundle\Form\Subscriber\ChannelSubscriber;

/**
 * Tests related class
 *
 * @author    Antoine Guigan <antoine@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 */
class ChannelSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function getTestAddCodeFieldData()
    {
        return array(
            array(null),
            array(false),
            array(1)
        );
    }

    /**
     * @dataProvider getTestAddCodeFieldData
     */
    public function testAddCodeField($id)
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($form));

        if ($id === null) {
            $event->expects($this->once())
                ->method('getData')
                ->will($this->returnValue(null));
            $event->expects($this->never())
                ->method('getForm');
        } else {
            $channel = $this->getMock('Pim\Bundle\ProductBundle\Entity\Product');
            $event->expects($this->once())
                ->method('getData')
                ->will($this->returnValue($channel));
            $channel->expects($this->once())
                ->method('getId')
                ->will($this->returnValue($id));
            $form->expects($this->once())
                ->method('add')
                ->with(
                    $this->equalTo('code'),
                    $this->equalTo('text'),
                    $this->equalTo(array('disabled'=>(bool) $id))
                );
        }
        $subscriber = new ChannelSubscriber();
        $subscriber->addCodeField($event);
    }
}
