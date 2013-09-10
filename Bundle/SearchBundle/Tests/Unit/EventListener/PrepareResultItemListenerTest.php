<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\EventListener;

use Oro\Bundle\SearchBundle\EventListener\PrepareResultItemListener;

class PrepareResultItemListenerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PrepareResultItemListener
     */
    protected $listener;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $router;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $mapper;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $em;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $event;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $item;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $entity;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->router = $this->getMockBuilder('Symfony\Component\Routing\Router')
            ->disableOriginalConstructor()
            ->getMock();

        $this->mapper = $this->getMockBuilder('Oro\Bundle\SearchBundle\Engine\ObjectMapper')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->item = $this->getMockBuilder('Oro\Bundle\SearchBundle\Query\Result\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $this->event = $this->getMockBuilder('Oro\Bundle\SearchBundle\Event\PrepareResultItemEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $this->entity = $this->getMockBuilder('Oro\Bundle\UserBundle\Entity\User')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new PrepareResultItemListener($this->router, $this->mapper, $this->em);
    }

    /**
     * Check that process data doesn't execute any query if url and title already set
     */
    public function testProcessSetData()
    {
        $this->event->expects($this->once())
            ->method('getEntity');

        $this->event->expects($this->once())
            ->method('getResultItem')
            ->will($this->returnValue($this->item));

        $this->item->expects($this->once())
            ->method('getRecordUrl')
            ->will($this->returnValue('url'));

        $this->item->expects($this->once())
            ->method('getRecordTitle')
            ->will($this->returnValue('title'));

        $this->em->expects($this->never())
            ->method('getRepository');

        $this->listener->process($this->event);
    }

    /**
     * Generates url from existed entity
     */
    public function testProcessUrlFromEntity()
    {
        $this->event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($this->entity));

        $this->event->expects($this->once())
            ->method('getResultItem')
            ->will($this->returnValue($this->item));

        $this->item->expects($this->once())
            ->method('getRecordUrl')
            ->will($this->returnValue(false));

        $this->item->expects($this->once())
            ->method('getRecordTitle')
            ->will($this->returnValue('title'));

        $this->item->expects($this->once())
            ->method('getEntityName')
            ->will($this->returnValue(get_class($this->entity)));

        $metadataMock = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($this->entity))
            ->will($this->returnValue($metadataMock));

        $this->mapper->expects($this->exactly(2))
            ->method('getEntityMapParameter')
            ->with(get_class($this->entity), 'route')
            ->will($this->returnValue(array('parameters' => array('parameter' => 'field'), 'name' => 'test_route')));

        $this->mapper->expects($this->once())
            ->method('getFieldValue')
            ->with($this->entity, 'field')
            ->will($this->returnValue('test_data'));

        $this->router->expects($this->once())
            ->method('generate')
            ->with('test_route', array('parameter' => 'test_data'), true)
            ->will($this->returnValue('test_url'));

        $this->listener->process($this->event);
    }

    /**
     * Process entity without URL params
     */
    public function testProcessEmptyUrl()
    {
        $this->event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($this->entity));

        $this->event->expects($this->once())
            ->method('getResultItem')
            ->will($this->returnValue($this->item));

        $this->item->expects($this->once())
            ->method('getRecordUrl')
            ->will($this->returnValue(false));

        $this->item->expects($this->once())
            ->method('getRecordTitle')
            ->will($this->returnValue('title'));

        $this->item->expects($this->once())
            ->method('getEntityName')
            ->will($this->returnValue(get_class($this->entity)));

        $metadataMock = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($this->entity))
            ->will($this->returnValue($metadataMock));

        $this->mapper->expects($this->once())
            ->method('getEntityMapParameter')
            ->with(get_class($this->entity), 'route')
            ->will($this->returnValue(false));

        $this->item->expects($this->once())
            ->method('setRecordUrl')
            ->with('');

        $this->listener->process($this->event);
    }

    /**
     * Trying to find entity and generates parameters from result item
     */
    public function testProcessUrl()
    {
        $this->event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue(false));

        $this->event->expects($this->once())
            ->method('getResultItem')
            ->will($this->returnValue($this->item));

        $this->item->expects($this->once())
            ->method('getRecordUrl')
            ->will($this->returnValue(false));

        $this->item->expects($this->once())
            ->method('getRecordTitle')
            ->will($this->returnValue('title'));

        $this->item->expects($this->once())
            ->method('getEntityName')
            ->will($this->returnValue(get_class($this->entity)));

        $metadataMock = $this->getMockBuilder('\Doctrine\ORM\Mapping\ClassMetadata')
            ->disableOriginalConstructor()
            ->getMock();

        $this->em->expects($this->once())
            ->method('getClassMetadata')
            ->with(get_class($this->entity))
            ->will($this->returnValue($metadataMock));

        $repositoryMock = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock->expects($this->once())
            ->method('find')
            ->will($this->returnValue(false));

        $this->em->expects($this->once())
            ->method('getRepository')
            ->will($this->returnValue($repositoryMock));

        $this->item->expects($this->exactly(2))
            ->method('getRecordId')
            ->will($this->returnValue(1));

        $this->mapper->expects($this->exactly(2))
            ->method('getEntityMapParameter')
            ->with(get_class($this->entity), 'route')
            ->will($this->returnValue(array('parameters' => array('parameter' => 'field'), 'name' => 'test_route')));

        $this->router->expects($this->once())
            ->method('generate')
            ->with('test_route', array('parameter' => '1'), true)
            ->will($this->returnValue('test_url'));

        $this->listener->process($this->event);
    }

    /**
     * Process entity without predefined title fields
     */
    public function testProcessTitleDefaultBehavior()
    {
        $this->event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue($this->entity));

        $this->event->expects($this->once())
            ->method('getResultItem')
            ->will($this->returnValue($this->item));

        $this->item->expects($this->once())
            ->method('getRecordUrl')
            ->will($this->returnValue('url'));

        $this->item->expects($this->once())
            ->method('getRecordTitle')
            ->will($this->returnValue(false));

        $this->item->expects($this->once())
            ->method('getEntityName')
            ->will($this->returnValue(get_class($this->entity)));

        $this->mapper->expects($this->once())
            ->method('getEntityMapParameter')
            ->with(get_class($this->entity), 'title_fields')
            ->will($this->returnValue(false));

        $this->entity->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('testTitle'));

        $this->item->expects($this->once())
            ->method('setRecordTitle')
            ->with('testTitle');

        $this->listener->process($this->event);
    }

    /**
     * Process loading entity and using fields for title
     */
    public function testProcessTitle()
    {
        $this->event->expects($this->once())
            ->method('getEntity')
            ->will($this->returnValue(false));

        $this->event->expects($this->once())
            ->method('getResultItem')
            ->will($this->returnValue($this->item));

        $this->item->expects($this->once())
            ->method('getRecordUrl')
            ->will($this->returnValue('url'));

        $this->item->expects($this->once())
            ->method('getRecordTitle')
            ->will($this->returnValue(false));

        $this->item->expects($this->once())
            ->method('getEntityName')
            ->will($this->returnValue(get_class($this->entity)));

        $this->mapper->expects($this->exactly(2))
            ->method('getEntityMapParameter')
            ->with(get_class($this->entity), 'title_fields')
            ->will($this->returnValue(array('testField')));

        $repositoryMock = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryMock->expects($this->once())
            ->method('find')
            ->will($this->returnValue($this->entity));

        $this->em->expects($this->once())
            ->method('getRepository')
            ->with(get_class($this->entity))
            ->will($this->returnValue($repositoryMock));

        $this->mapper->expects($this->once())
            ->method('getFieldValue')
            ->with($this->entity, 'testField')
            ->will($this->returnValue('testTitle'));

        $this->item->expects($this->once())
            ->method('setRecordTitle')
            ->with('testTitle');

        $this->listener->process($this->event);
    }
}
