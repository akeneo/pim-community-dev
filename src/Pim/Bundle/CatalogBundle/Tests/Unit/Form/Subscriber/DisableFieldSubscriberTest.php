<?php

namespace Pim\Bundle\CatalogBundle\Tests\Unit\Form\Subscriber;

use Pim\Bundle\CatalogBundle\Form\Subscriber\DisableFieldSubscriber;

/**
 * Test related class
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class DisableFieldSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function getTestAddCodeFieldData()
    {
        return [
            [null],
            [false],
            [1]
        ];
    }

    /**
     * @param integer $id
     *
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
        $field = $this->getMockBuilder('Symfony\Component\Form\Form')
            ->disableOriginalConstructor()
            ->getMock();
        $config = $this->getMock('Symfony\Component\Form\FormConfigInterface');

        $event->expects($this->any())
            ->method('getForm')
            ->will($this->returnValue($form));

        $form->expects($this->any())
            ->method('get')
            ->will($this->returnValue($field));

        $field->expects($this->any())
            ->method('getConfig')
            ->will($this->returnValue($config));

        if ($id === null) {
            $event->expects($this->once())
                ->method('getData')
                ->will($this->returnValue($id));
            $event->expects($this->never())
                ->method('getForm');
        } else {
            $channel = $this->getMock('Pim\Bundle\CatalogBundle\Entity\Channel');
            $event->expects($this->once())
                ->method('getData')
                ->will($this->returnValue($channel));
            $channel->expects($this->once())
                ->method('getId')
                ->will($this->returnValue($id));
            if ($id) {
                $form->expects($this->once())
                    ->method('add')
                    ->with(
                        $this->equalTo('code'),
                        $this->equalTo(null),
                        $this->equalTo(['disabled' => true, 'read_only' => true])
                    );
            } else {
                $form->expects($this->never())
                    ->method('add');
            }
        }
        $subscriber = new DisableFieldSubscriber('code');
        $subscriber->postSetData($event);
    }
}
