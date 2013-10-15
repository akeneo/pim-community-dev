<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityExtendBundle\Form\EventListener\TargetFieldSubscriber;

class TargetFieldSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testPreSetSubmitData()
    {
        $data = array('target_field' => 'firstName');

        $targetFieldMock = $this->getMockBuilder('Oro\Bundle\EntityExtendBundle\Form\Type\TargetFieldType')
            ->disableOriginalConstructor()
            ->setMethods(array('getConfig', 'getOptions', 'getData', 'get'))
            ->getMock();
        $targetFieldMock
            ->expects($this->any())
            ->method('getConfig')
            ->will($this->returnSelf());
        $targetFieldMock
            ->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue(array('auto_initialize' => true)));
        $targetFieldMock
            ->expects($this->exactly(3))
            ->method('get')
            ->with($this->equalTo('label'))
            ->will($this->returnValue(null));

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->setMethods(array('getParent', 'getData'))
            ->getMockForAbstractClass();
        $formMock
            ->expects($this->exactly(4))
            ->method('getParent')
            ->will($this->returnSelf());
        $formMock
            ->expects($this->exactly(2))
            ->method('get')
            ->with($this->logicalOr($this->equalTo('target_field'), $this->equalTo('target_entity')))
            ->will($this->returnValue($targetFieldMock));
        $formMock
            ->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($data));

        /** @var FormEvent $event */
        $event = new FormEvent($formMock, $data);

        $request = new Request(
            $query   = array(),
            $request = array(
                'oro_entity_config_type' => array(
                    'extend' => array(
                        'target_entity' => 'Oro\Bundle\UserBundle\Entity\User',
                        'target_field'  => 'firstName'
                    )
                )
            )
        );

        $configManagerMock = $this->getMockBuilder('Oro\Bundle\EntityConfigBundle\Config\ConfigManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository', 'getProvider', 'getEntityManager'))
            ->getMock();

        $repositoryMock = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('findOneBy', 'findBy', 'getId'))
            ->getMock();
        $repositoryMock->expects($this->once())->method('findOneBy')->will($this->returnSelf());
        $repositoryMock->expects($this->once())->method('getId')->will($this->returnValue(1));
        $repositoryMock->expects($this->once())->method('findBy')->will(
            $this->returnValue(
                array(
                    new FieldConfigId('Oro\Bundle\UserBundle\Entity\User', 'entity', 'firstName'),
                    new FieldConfigId('Oro\Bundle\UserBundle\Entity\User', 'entity', 'lastName'),
                    new FieldConfigId('Oro\Bundle\UserBundle\Entity\User', 'entity', 'email'),
                )
            )
        );

        $configManagerMock->expects($this->exactly(2))->method('getEntityManager')->will($this->returnSelf());
        $configManagerMock->expects($this->once())->method('getProvider')->will($this->returnValue($targetFieldMock));
        $configManagerMock
            ->expects($this->exactly(2))
            ->method('getRepository')
            ->will($this->returnValue($repositoryMock));

        /** @var TargetFieldSubscriber $listener */
        $listener = new TargetFieldSubscriber($request, $configManagerMock);
        $listener->preSetSubmitData($event);

        $this->assertEquals($data, $event->getData());
    }

    public function testGetSubscribedEvents()
    {
        $this->assertEquals(
            array(
                FormEvents::PRE_SET_DATA => 'preSetSubmitData',
                FormEvents::PRE_SUBMIT   => 'preSetSubmitData'
            ),
            TargetFieldSubscriber::getSubscribedEvents()
        );
    }
}
