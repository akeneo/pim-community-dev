<?php

namespace Oro\Bundle\OrganizationBundle\Tests\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Common\Util\ClassUtils;

use Oro\Bundle\OrganizationBundle\Form\EventListener\OwnerFormSubscriber;
use Oro\Bundle\UserBundle\Entity\User;
use OroCRM\Bundle\ContactBundle\Entity\Contact;

class OwnerFormSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerRegistry;

    /**
     * @var string
     */
    protected $fieldName = 'owner';

    /**
     * @var string
     */
    protected $fieldLabel = 'Owner';

    /**
     * @var User
     */
    protected $defaultOwner;

    /**
     * @var OwnerFormSubscriber
     */
    protected $subscriber;

    protected function setUp()
    {
        $this->managerRegistry = $this->getMockBuilder('Doctrine\Common\Persistence\ManagerRegistry')
            ->getMockForAbstractClass();

        $this->defaultOwner = new User();

        $this->subscriber = new OwnerFormSubscriber(
            $this->managerRegistry,
            $this->fieldName,
            $this->fieldLabel,
            true,
            $this->defaultOwner
        );
    }

    protected function tearDown()
    {
        unset($this->managerRegistry);
        unset($this->defaultOwner);
        unset($this->subscriber);
    }

    public function testGetSubscribedEvents()
    {
        $expectedEvents = array(FormEvents::POST_SET_DATA => 'postSetData');
        $this->assertEquals($expectedEvents, $this->subscriber->getSubscribedEvents());
    }

    public function testPostSetDataNotRootForm()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('getParent')->will($this->returnValue(true));
        $form->expects($this->never())->method('has');

        $event = new FormEvent($form, null);
        $this->subscriber->postSetData($event);
    }

    public function testPostSetDataNoOwnerField()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('getParent')->will($this->returnValue(false));
        $form->expects($this->once())->method('has')->with($this->fieldName)->will($this->returnValue(false));
        $this->managerRegistry->expects($this->never())->method('getManagerForClass');

        $event = new FormEvent($form, new \DateTime());
        $this->subscriber->postSetData($event);
    }

    public function testPostSetDataNotAnObject()
    {
        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('getParent')->will($this->returnValue(false));
        $form->expects($this->once())->method('has')->with($this->fieldName)->will($this->returnValue(true));
        $this->managerRegistry->expects($this->never())->method('getManagerForClass');

        $event = new FormEvent($form, array(1, 2, 3));
        $this->subscriber->postSetData($event);
    }

    public function testPostSetDataNotManagedObject()
    {
        $data = new \DateTime();

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('getParent')->will($this->returnValue(false));
        $form->expects($this->once())->method('has')->with($this->fieldName)->will($this->returnValue(true));
        $this->managerRegistry->expects($this->once())->method('getManagerForClass')
            ->with(get_class($data))->will($this->returnValue(null));

        $event = new FormEvent($form, $data);
        $this->subscriber->postSetData($event);
    }

    public function testPostSetDataReplaceOwnerAssignGranted()
    {
        $data = new Contact();

        $this->prepareEntityManager($data);

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('getParent')->will($this->returnValue(false));
        $form->expects($this->once())->method('has')->with($this->fieldName)->will($this->returnValue(true));
        $form->expects($this->never())->method('get');

        $event = new FormEvent($form, $data);
        $this->subscriber->postSetData($event);
    }

    public function testPostSetDataReplaceOwnerAssignNotGranted()
    {
        $data = new Contact();
        $ownerName = 'user';
        $owner = new User();
        $owner->setUsername($ownerName);

        $this->prepareEntityManager($data);

        $ownerForm = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $ownerForm->expects($this->once())->method('getData')->will($this->returnValue($owner));

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('getParent')->will($this->returnValue(false));
        $form->expects($this->once())->method('has')->with($this->fieldName)->will($this->returnValue(true));
        $form->expects($this->once())->method('get')->with($this->fieldName)->will($this->returnValue($ownerForm));
        $form->expects($this->once())->method('remove')->with($this->fieldName);
        $form->expects($this->once())->method('add')->with(
            $this->fieldName,
            'text',
            array(
                'disabled' => true,
                'data' => $ownerName,
                'mapped' => false,
                'required' => false,
                'label' => $this->fieldLabel
            )
        );

        $this->subscriber = new OwnerFormSubscriber(
            $this->managerRegistry,
            $this->fieldName,
            $this->fieldLabel,
            false, // assign is not granted
            $this->defaultOwner
        );

        $event = new FormEvent($form, $data);
        $this->subscriber->postSetData($event);
    }

    protected function prepareEntityManager($entity)
    {
        $entityClass = ClassUtils::getClass($entity);

        $classMetadata = $this->getMockBuilder('Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()->getMock();
        $classMetadata->expects($this->once())->method('getIdentifierValues')
            ->with($entity)->will($this->returnValue(array(1)));
        $entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')->disableOriginalConstructor()->getMock();
        $entityManager->expects($this->once())->method('getClassMetadata')
            ->with($entityClass)->will($this->returnValue($classMetadata));
        $this->managerRegistry->expects($this->once())->method('getManagerForClass')
            ->with($entityClass)->will($this->returnValue($entityManager));
    }

    public function testPostSetDataSetPredefinedOwnerExists()
    {
        $ownerForm = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $ownerForm->expects($this->once())->method('getData')->will($this->returnValue(new User()));
        $ownerForm->expects($this->never())->method('setData');

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('getParent')->will($this->returnValue(false));
        $form->expects($this->once())->method('has')->with($this->fieldName)->will($this->returnValue(true));
        $form->expects($this->once())->method('get')->with($this->fieldName)->will($this->returnValue($ownerForm));

        $event = new FormEvent($form, null);
        $this->subscriber->postSetData($event);
    }

    public function testPostSetDataSetPredefinedOwnerNotExists()
    {
        $ownerForm = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $ownerForm->expects($this->once())->method('getData')->will($this->returnValue(null));
        $ownerForm->expects($this->once())->method('setData')->with($this->defaultOwner);

        $form = $this->getMockBuilder('Symfony\Component\Form\Form')->disableOriginalConstructor()->getMock();
        $form->expects($this->once())->method('getParent')->will($this->returnValue(false));
        $form->expects($this->once())->method('has')->with($this->fieldName)->will($this->returnValue(true));
        $form->expects($this->once())->method('get')->with($this->fieldName)->will($this->returnValue($ownerForm));

        $event = new FormEvent($form, null);
        $this->subscriber->postSetData($event);
    }
}
