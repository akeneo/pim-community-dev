<?php

namespace Oro\Bundle\SearchBundle\Tests\Unit\Datagrid;

use Oro\Bundle\SearchBundle\Datagrid\EntityResultListener;
use Oro\Bundle\GridBundle\EventDispatcher\ResultDatagridEvent;
use Oro\Bundle\GridBundle\Datagrid\DatagridInterface;
use Oro\Bundle\SearchBundle\Query\Result\Item;

class EntityResultListenerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_DATAGRID_NAME = 'test_datagrid_name';
    const TEST_ENTITY_NAME   = 'test_entity_name';

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $dispatcher;

    /**
     * Set up test environment
     */
    public function setUp()
    {
        $this->dispatcher = $this->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcher')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param $datagridName
     * @return DatagridInterface
     */
    protected function getDatagridMock($datagridName)
    {
        $datagrid = $this->getMockForAbstractClass(
            'Oro\Bundle\GridBundle\Datagrid\DatagridInterface',
            array(),
            '',
            false,
            true,
            true,
            array('getName')
        );
        $datagrid->expects($this->once())
            ->method('getName')
            ->will($this->returnValue($datagridName));

        return $datagrid;
    }

    public function testProcessResultNotMatchedDatagrid()
    {
        $datagrid = $this->getDatagridMock('random_datagrid_name');

        $resultFormatter = $this->getMock(
            'Oro\Bundle\SearchBundle\Formatter\ResultFormatter',
            array('getResultEntities'),
            array(),
            '',
            false
        );
        $resultFormatter->expects($this->never())
            ->method('getResultEntities');

        $event = new ResultDatagridEvent($datagrid);

        $eventListener = new EntityResultListener($resultFormatter, self::TEST_DATAGRID_NAME, $this->dispatcher);
        $eventListener->processResult($event);
    }

    public function testProcessResult()
    {
        $datagrid = $this->getDatagridMock(self::TEST_DATAGRID_NAME);

        $objectManager = $this->getMockForAbstractClass(
            'Doctrine\Common\Persistence\ObjectManager',
            array(),
            '',
            false
        );

        $firstItem = new Item($objectManager, self::TEST_ENTITY_NAME, 1);
        $secondItem = new Item($objectManager, self::TEST_ENTITY_NAME, 2, 'title', 'url');
        $providerItems = array($firstItem, $secondItem);

        $firstEntity = new \stdClass();
        $secondEntity = new \stdClass();
        $providerEntities = array(
            self::TEST_ENTITY_NAME => array(
                1 => $firstEntity,
                2 => $secondEntity,
            )
        );

        $resultFormatter = $this->getMock(
            'Oro\Bundle\SearchBundle\Formatter\ResultFormatter',
            array('getResultEntities'),
            array(),
            '',
            false
        );
        $resultFormatter->expects($this->once())
            ->method('getResultEntities')
            ->with($providerItems)
            ->will($this->returnValue($providerEntities));

        $event = new ResultDatagridEvent($datagrid);
        $event->setRows($providerItems);

        // test
        $eventListener = new EntityResultListener($resultFormatter, self::TEST_DATAGRID_NAME, $this->dispatcher);
        $eventListener->processResult($event);

        $expectedRows = array(
            array(
                'indexer_item' => $firstItem,
                'entity' => $firstEntity,
            ),
            array(
                'indexer_item' => $secondItem,
                'entity' => $secondEntity,
            ),
        );

        $this->assertEquals($expectedRows, $event->getRows());
    }
}
