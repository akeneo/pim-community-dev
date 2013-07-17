<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\EventListener;

use Oro\Bundle\AddressBundle\Form\EventListener\AddressCollectionTypeSubscriber;
use Symfony\Component\Form\FormEvents;

class AddressCollectionTypeSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $typedAddressClass;

    /**
     * @var AddressCollectionTypeSubscriber
     */
    protected $subscriber;

    /**
     * SetUp test environment
     */
    public function setUp()
    {
        $this->typedAddressClass = $this->getMockClass('Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress');
        $this->subscriber = new AddressCollectionTypeSubscriber('test', $this->typedAddressClass);
    }

    public function testGetSubscribedEvents()
    {
        $result = $this->subscriber->getSubscribedEvents();

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $result);
        $this->assertArrayHasKey(FormEvents::PRE_BIND, $result);
        $this->assertArrayHasKey(FormEvents::POST_BIND, $result);
    }

    public function testPreSetNotEmpty()
    {
        $addresses = $this->getMockBuilder('Doctrine\Common\Collections\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $addresses->expects($this->once())
            ->method('isEmpty')
            ->will($this->returnValue(false));
        $addresses->expects($this->never())
            ->method('add');
        $this->subscriber->preSet($this->getEvent($addresses));
    }

    public function testPreSetEmpty()
    {
        $addresses = $this->getMockBuilder('Doctrine\Common\Collections\Collection')
            ->disableOriginalConstructor()
            ->getMock();
        $addresses->expects($this->once())
            ->method('isEmpty')
            ->will($this->returnValue(true));

        $this->subscriber->preSet(
            $this->getEvent(
                $addresses,
                array($this->getMock('Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress'))
            )
        );
    }

    public function testPostBind()
    {
        $addressEmpty = $this->getMock('Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress');
        $addressEmpty->expects($this->once())
            ->method('isEmpty')
            ->will($this->returnValue(true));
        $addressNotEmpty = $this->getMock('Oro\Bundle\AddressBundle\Entity\AbstractTypedAddress');
        $addressNotEmpty->expects($this->once())
            ->method('isEmpty')
            ->will($this->returnValue(false));

        $addresses = new \Doctrine\Common\Collections\ArrayCollection(array($addressEmpty, $addressNotEmpty));
        $this->subscriber->postBind(
            $this->getEvent(
                $addresses,
                new \Doctrine\Common\Collections\ArrayCollection(array(1 => $addressNotEmpty))
            )
        );
    }

    protected function getEvent($expectedGetDataValue, $expectedSetDataValue = null)
    {
        $data = $this->getMockBuilder('\stdClass')
            ->setMethods(array('getTest', 'setTest'))
            ->getMock();

        $data->expects($this->once())
            ->method('getTest')
            ->will($this->returnValue($expectedGetDataValue));

        if (null !== $expectedSetDataValue) {
            $data->expects($this->once())
                ->method('setTest')
                ->with($expectedSetDataValue);
        }

        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        return $event;
    }

    /**
     * @dataProvider noDataPreBindDataProvider
     * @param array|null $data
     */
    public function testPreBindNoData($data)
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));
        $event->expects($this->never())
            ->method('setData');
        $this->subscriber->preBind($event);
    }

    /**
     * @return array
     */
    public function noDataPreBindDataProvider()
    {
        return array(
            array(
                null, array()
            ),
            array(
                array(), array()
            )
        );
    }

    /**
     * @dataProvider preBindDataProvider
     * @param array|null $data
     * @param array $expected
     */
    public function testPreBind($data, $expected)
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));
        $event->expects($this->once())
            ->method('setData')
            ->with($expected);
        $this->subscriber->preBind($event);
    }

    public function preBindDataProvider()
    {
        return array(
            array(
                array('key' => 'value', 'test' => array(array(), array('k' => 'v'))),
                array('key' => 'value', 'test' => array(array('k' => 'v', 'primary' => true)))
            ),
            array(
                array('key' => 'value', 'test' => array(array(array()), array('k' => 'v'))),
                array('key' => 'value', 'test' => array(array('k' => 'v', 'primary' => true)))
            ),
            array(
                array('key' => 'value', 'test' => array(array(array('k2' => 'v')), array('k' => 'v'))),
                array('key' => 'value', 'test' => array(array(array('k2' => 'v'), 'primary' => true), array('k' => 'v')))
            ),
        );
    }

    /**
     * @dataProvider preBindNoResetDataProvider
     * @param array $data
     */
    public function testPreBindNoReset($data)
    {
        $event = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();
        $event->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));
        $event->expects($this->never())
            ->method('setData');
        $this->subscriber->preBind($event);
    }

    /**
     * @return array
     */
    public function preBindNoResetDataProvider()
    {
        return array(
            array(
                array('key' => 'value')
            ),
            array(
                array('key' => 'value', 'test' => array())
            )
        );
    }
}
