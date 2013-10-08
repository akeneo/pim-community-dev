<?php

namespace Oro\Bundle\EntityExtendBundle\Tests\Unit\Form\EventListener;

use Oro\Bundle\EntityExtendBundle\Form\EventListener\TargetFieldSubscriber;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\HttpFoundation\Request;

class TargetFieldSubscriberTest extends \PHPUnit_Framework_TestCase
{
    public function testPreSetSubmitData()
    {
        $data         = array();
        $expectedData = array();

        $targetFieldMock = $this->getMockBuilder('Oro\Bundle\EntityExtendBundle\Form\Type\TargetFieldType')
            ->disableOriginalConstructor()
            ->setMethods(array('getConfig', 'getOptions', 'getData'))
            ->getMock();
        $targetFieldMock
            ->expects($this->any())
            ->method('getConfig')
            ->will($this->returnSelf());
        $targetFieldMock
            ->expects($this->any())
            ->method('getOptions')
            ->will($this->returnValue(array('auto_initialize' => true)));

        $formMock = $this->getMockBuilder('Symfony\Component\Form\Test\FormInterface')
            ->setMethods(array('getParent'))
            ->getMockForAbstractClass();
        $formMock
            ->expects($this->exactly(4))
            ->method('getParent')
            ->will($this->returnSelf());
        $formMock
            ->expects($this->exactly(2))
            ->method('get')
            ->with(
                $this->logicalOr(
                    $this->equalTo('target_field'),
                    $this->equalTo('target_entity')
                )
            )
            ->will($this->returnValue($targetFieldMock));


        /** @var FormEvent $event */
        $event = new FormEvent($formMock, $data);

        $request = new Request(
            $query   = array(),
            $request = array(
                'oro_entity_config_type' => array(
                    'extend' => array(
                        'target_entity' => 'Oro\Bundle\UserBundle\Entity\User'
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
            ->setMethods(array('findOneBy'))
            ->getMock();
//        $repositoryMock
//            ->expects($this->once())
//            ->method('get')

        $configManagerMock
            ->expects($this->once())
            ->method('getEntityManager')
            ->will($this->returnSelf());
        $configManagerMock
            ->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repositoryMock));

        /** @var TargetFieldSubscriber $listener */
        $listener = new TargetFieldSubscriber($request, $configManagerMock);
        $listener->preSetSubmitData($event);

        //var_dump($event->getData());
        //$this->assertEquals($expectedData, $event->getData());
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
