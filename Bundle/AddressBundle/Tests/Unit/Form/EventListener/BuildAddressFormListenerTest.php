<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\EventListener;

use Oro\Bundle\AddressBundle\Form\EventListener\BuildAddressFormListener;
use Symfony\Component\Form\FormEvents;

class BuildAddressFormListenerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Doctrine\Common\Persistence\ObjectManager */
    protected $om;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    protected $formBuilder;

    /**
     * @var BuildAddressFormListener
     */
    protected $listener;

    /**
     * SetUp test environment
     */
    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->formBuilder = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $this->listener = new BuildAddressFormListener($this->om, $this->formBuilder);
    }

    public function testGetSubscribedEvents()
    {
        $result = $this->listener->getSubscribedEvents();

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $result);
        $this->assertArrayHasKey(FormEvents::PRE_BIND, $result);
    }

    public function testPreSetDataEmptyAddress()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(null));
        $eventMock->expects($this->once())
            ->method('getForm');

        $this->assertEquals(null, $this->listener->preSetData($eventMock));
    }

    public function testPreSetDataEmptyCountry()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $addressMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Address');
        $addressMock->expects($this->once())
            ->method('getCountry')
            ->will($this->returnValue(null));

        $eventMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($addressMock));
        $eventMock->expects($this->once())
            ->method('getForm');

        $this->assertEquals(null, $this->listener->preSetData($eventMock));
    }

    public function testPreSetDataHasState()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $countryMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Country');
        $countryMock->expects($this->once())
            ->method('hasRegions')
            ->will($this->returnValue(true));

        $addressMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Address');
        $addressMock->expects($this->once())
            ->method('getCountry')
            ->will($this->returnValue($countryMock));
        $addressMock->expects($this->once())
            ->method('getState');

        $configMock = $this->getMock('Symfony\Component\Form\FormConfigInterface');
        $configMock->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue(array()));
        
        $fieldMock = $this->getMockBuilder('Symfony\Component\Form\Tests\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Tests\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->once())
            ->method('has')
            ->with($this->equalTo('state'))
            ->will($this->returnValue(true));
        $formMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('state'))
            ->will($this->returnValue($fieldMock));
        $formMock->expects($this->once())
            ->method('add');

        $fieldMock->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($configMock));

        $newFieldMock = $this->getMockBuilder('Symfony\Component\Form\Tests\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->formBuilder->expects($this->once())
            ->method('createNamed')
            ->will($this->returnValue($newFieldMock));

        $eventMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($addressMock));
        $eventMock->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($formMock));

        $this->assertNull($this->listener->preSetData($eventMock));
    }

    public function testPreSetDataNoState()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $countryMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Country');
        $countryMock->expects($this->once())
            ->method('hasRegions')
            ->will($this->returnValue(true));

        $addressMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Address');
        $addressMock->expects($this->once())
            ->method('getCountry')
            ->will($this->returnValue($countryMock));
        $addressMock->expects($this->once())
            ->method('getState');

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Tests\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->once())
            ->method('has')
            ->with($this->equalTo('state'))
            ->will($this->returnValue(false));
        $formMock->expects($this->never())
            ->method('get');
        $formMock->expects($this->once())
            ->method('add');

        $newFieldMock = $this->getMockBuilder('Symfony\Component\Form\Tests\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->formBuilder->expects($this->once())
            ->method('createNamed')
            ->will($this->returnValue($newFieldMock));

        $eventMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($addressMock));
        $eventMock->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($formMock));

        $this->assertNull($this->listener->preSetData($eventMock));
    }

    public function testPreBindData()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $countryMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Country');
        $countryMock->expects($this->once())
            ->method('hasRegions')
            ->will($this->returnValue(true));

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository
            ->expects($this->any())
            ->method('find')
            ->will($this->returnValue($countryMock));

        $this->om->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo('OroAddressBundle:Country'))
            ->will($this->returnValue($repository));

        $configMock = $this->getMock('Symfony\Component\Form\FormConfigInterface');
        $configMock->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue(array()));

        $fieldMock = $this->getMockBuilder('Symfony\Component\Form\Tests\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $fieldMock->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($configMock));

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Tests\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('state'))
            ->will($this->returnValue($fieldMock));
        $formMock->expects($this->once())
            ->method('add');

        $newFieldMock = $this->getMockBuilder('Symfony\Component\Form\Tests\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->formBuilder->expects($this->once())
            ->method('createNamed')
            ->will($this->returnValue($newFieldMock));

        $eventMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(array()));
        $eventMock->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($formMock));

        $this->assertEquals(null, $this->listener->preBind($eventMock));
    }
}
