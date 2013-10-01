<?php

namespace Oro\Bundle\AddressBundle\Tests\Unit\EventListener;

use Oro\Bundle\AddressBundle\Form\EventListener\AddressCountryAndRegionSubscriber;
use Symfony\Component\Form\FormEvents;

class AddressCountryAndRegionSubscriberTest extends \PHPUnit_Framework_TestCase
{
    const TEST_COUNTRY_NAME = 'testCountry';

    /** @var \Doctrine\Common\Persistence\ObjectManager */
    protected $om;

    /** @var \Symfony\Component\Form\FormFactoryInterface */
    protected $formBuilder;

    /**
     * @var AddressCountryAndRegionSubscriber
     */
    protected $subscriber;

    /**
     * SetUp test environment
     */
    public function setUp()
    {
        $this->om = $this->getMock('Doctrine\Common\Persistence\ObjectManager');
        $this->formBuilder = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $this->subscriber = new AddressCountryAndRegionSubscriber($this->om, $this->formBuilder);
    }

    public function testGetSubscribedEvents()
    {
        $result = $this->subscriber->getSubscribedEvents();

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

        $this->assertEquals(null, $this->subscriber->preSetData($eventMock));
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

        $this->assertEquals(null, $this->subscriber->preSetData($eventMock));
    }

    public function testPreSetDataHasState()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $countryMock = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()->getMock();
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

        $fieldMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
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

        $newFieldMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
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

        $this->assertNull($this->subscriber->preSetData($eventMock));
    }

    public function testPreSetDataNoState()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $countryMock = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()->getMock();
        $countryMock->expects($this->once())
            ->method('hasRegions')
            ->will($this->returnValue(true));

        $addressMock = $this->getMock('Oro\Bundle\AddressBundle\Entity\Address');
        $addressMock->expects($this->once())
            ->method('getCountry')
            ->will($this->returnValue($countryMock));
        $addressMock->expects($this->once())
            ->method('getState');

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
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

        $newFieldMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
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

        $this->assertNull($this->subscriber->preSetData($eventMock));
    }

    public function testPreSubmitData()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()->getMock();

        $countryMock = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()->getMock();
        $countryMock->expects($this->once())
            ->method('hasRegions')
            ->will($this->returnValue(true));

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->any())->method('find')->with(self::TEST_COUNTRY_NAME)
            ->will($this->returnValue($countryMock));

        $this->om->expects($this->once())->method('getRepository')->with($this->equalTo('OroAddressBundle:Country'))
            ->will($this->returnValue($repository));

        $configMock = $this->getMock('Symfony\Component\Form\FormConfigInterface');
        $configMock->expects($this->once())->method('getOptions')
            ->will($this->returnValue(array()));

        $fieldMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()->getMock();
        $fieldMock->expects($this->once())->method('getConfig')
            ->will($this->returnValue($configMock));

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()->getMock();
        $formMock->expects($this->once())->method('get')->with($this->equalTo('state'))
            ->will($this->returnValue($fieldMock));
        $formMock->expects($this->once())->method('add');

        $newFieldMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()->getMock();

        $this->formBuilder->expects($this->once())->method('createNamed')
            ->will($this->returnValue($newFieldMock));

        $startData = array('state_text' => 'stateText', 'country' => self::TEST_COUNTRY_NAME);
        $eventMock->expects($this->once())->method('getData')
            ->will($this->returnValue($startData));
        $eventMock->expects($this->once())->method('getForm')
            ->will($this->returnValue($formMock));

        $eventMock->expects($this->once())->method('setData')
            ->with(array_intersect_key($startData, array('country' => self::TEST_COUNTRY_NAME)));

        $this->subscriber->preSubmit($eventMock);
    }

    /**
     * Cover scenario when country has not any stored state and state filled as text field
     */
    public function testPreSubmitDataTextScenario()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()->getMock();

        $countryMock = $this->getMockBuilder('Oro\Bundle\AddressBundle\Entity\Country')
            ->disableOriginalConstructor()->getMock();
        $countryMock->expects($this->once())
            ->method('hasRegions')
            ->will($this->returnValue(false));

        $repository = $this->getMock('Doctrine\Common\Persistence\ObjectRepository');
        $repository->expects($this->any())->method('find')->with(self::TEST_COUNTRY_NAME)
            ->will($this->returnValue($countryMock));

        $this->om->expects($this->once())->method('getRepository')->with($this->equalTo('OroAddressBundle:Country'))
            ->will($this->returnValue($repository));


        $startData = array('state' => 'someState', 'state_text' => 'stateText', 'country' => self::TEST_COUNTRY_NAME);
        $eventMock->expects($this->once())->method('getData')
            ->will($this->returnValue($startData));

        $eventMock->expects($this->once())->method('setData')
            ->with(array_intersect_key($startData, array('state_text' => null, 'country' => self::TEST_COUNTRY_NAME)));

        $this->subscriber->preSubmit($eventMock);
    }
}
