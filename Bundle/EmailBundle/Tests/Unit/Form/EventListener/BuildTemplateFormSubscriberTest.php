<?php

namespace Oro\Bundle\EmailBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormEvents;

use Oro\Bundle\EmailBundle\Form\EventListener\BuildTemplateFormSubscriber;

class BuildTemplateFormSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $em;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $formBuilder;

    /**
     * @var BuildTemplateFormSubscriber
     */
    protected $listener;

    /**
     * SetUp test environment
     */
    public function setUp()
    {
        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formBuilder = $this->getMock('Symfony\Component\Form\FormFactoryInterface');

        $this->listener = new BuildTemplateFormSubscriber($this->em, $this->formBuilder);
    }

    public function testGetSubscribedEvents()
    {
        $result = $this->listener->getSubscribedEvents();

        $this->assertInternalType('array', $result);
        $this->assertArrayHasKey(FormEvents::PRE_SET_DATA, $result);
        $this->assertArrayHasKey(FormEvents::PRE_SUBMIT, $result);
    }

    public function testPreSetDataEmptyData()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $eventMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue(null));
        $eventMock->expects($this->once())
            ->method('getForm');

        $this->listener->preSetData($eventMock);
    }

    public function testPreSetDataEmptyEntityName()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $notificationMock = $this->getMock('Oro\Bundle\NotificationBundle\Entity\EmailNotification');
        $notificationMock->expects($this->once())
            ->method('getEntityName')
            ->will($this->returnValue(null));

        $eventMock->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($notificationMock));
        $eventMock->expects($this->once())
            ->method('getForm');

        $this->listener->preSetData($eventMock);
    }

    public function testPreSetDataHasTemplates()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $notificationMock = $this->getMock('Oro\Bundle\NotificationBundle\Entity\EmailNotification');
        $notificationMock->expects($this->once())
            ->method('getEntityName')
            ->will($this->returnValue('testEntity'));

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
            ->with($this->equalTo('template'))
            ->will($this->returnValue(true));
        $formMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('template'))
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
            ->will($this->returnValue($notificationMock));
        $eventMock->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($formMock));

        $this->listener->preSetData($eventMock);
    }

    public function testPreSetDataNoTemplates()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $notificationMock = $this->getMock('Oro\Bundle\NotificationBundle\Entity\EmailNotification');
        $notificationMock->expects($this->once())
            ->method('getEntityName')
            ->will($this->returnValue('testEntity'));

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->once())
            ->method('has')
            ->with($this->equalTo('template'))
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
            ->will($this->returnValue($notificationMock));
        $eventMock->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($formMock));

        $this->listener->preSetData($eventMock);
    }

    public function testPreSubmitData()
    {
        $eventMock = $this->getMockBuilder('Symfony\Component\Form\FormEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $configMock = $this->getMock('Symfony\Component\Form\FormConfigInterface');
        $configMock->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue(array()));

        $fieldMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $fieldMock->expects($this->once())
            ->method('getConfig')
            ->will($this->returnValue($configMock));

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $formMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('template'))
            ->will($this->returnValue($fieldMock));
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
            ->will($this->returnValue(array('entityName' => 'testEntityName')));
        $eventMock->expects($this->once())
            ->method('getForm')
            ->will($this->returnValue($formMock));

        $this->listener->preSubmit($eventMock);
    }
}
