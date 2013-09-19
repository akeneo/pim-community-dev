<?php

namespace Oro\Bundle\ImapBundle\Tests\Unit\Connector;

use Oro\Bundle\ImapBundle\Connector\ImapConnector;
use Oro\Bundle\ImapBundle\Connector\ImapConfig;
use Oro\Bundle\ImapBundle\Connector\ImapServices;

class ImapConnectorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ImapConnector */
    private $connector;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $storage;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $searchStringManager;

    protected function setUp()
    {
        $this->storage = $this->getMockBuilder('Oro\Bundle\ImapBundle\Mail\Storage\Imap')
            ->disableOriginalConstructor()
            ->getMock();
        $this->storage->expects($this->any())
            ->method('__destruct');

        $this->searchStringManager =
            $this->getMock('Oro\Bundle\ImapBundle\Connector\Search\SearchStringManagerInterface');
        $this->searchStringManager->expects($this->any())
            ->method('isAcceptableItem')
            ->will($this->returnValue(true));
        $this->searchStringManager->expects($this->any())
            ->method('buildSearchString')
            ->will($this->returnValue('some query'));

        $services = new ImapServices($this->storage, $this->searchStringManager);

        $factory = $this->getMockBuilder('Oro\Bundle\ImapBundle\Connector\ImapServicesFactory')
            ->disableOriginalConstructor()
            ->setMethods(array('createImapServices'))
            ->getMock();
        $factory->expects($this->once())
            ->method('createImapServices')
            ->will($this->returnValue($services));

        $this->connector = new ImapConnector(new ImapConfig(), $factory);
    }

    public function testGetSearchQueryBuilder()
    {
        $builder = $this->connector->getSearchQueryBuilder();
        $this->assertInstanceOf('Oro\Bundle\ImapBundle\Connector\Search\SearchQueryBuilder', $builder);
    }

    public function testFindItemsWithNoArguments()
    {
        $this->storage->expects($this->never())
            ->method('selectFolder');
        $this->storage->expects($this->never())
            ->method('search');
        $this->storage->expects($this->never())
            ->method('getMessage');

        $result = $this->connector->findItems();
        $this->assertCount(0, $result);
    }

    public function testFindItemsWithParentFolderOnly()
    {
        $this->storage->expects($this->at(0))
            ->method('selectFolder')
            ->with($this->equalTo('SomeFolder'));
        $this->storage->expects($this->never())
            ->method('search');
        $this->storage->expects($this->never())
            ->method('getMessage');

        $result = $this->connector->findItems('SomeFolder');
        $this->assertCount(0, $result);
    }

    public function testFindItemsWithParentFolderAndSearchQuery()
    {
        $this->storage->expects($this->at(0))
            ->method('selectFolder')
            ->with($this->equalTo('SomeFolder'));
        $this->storage->expects($this->at(1))
            ->method('search')
            ->with($this->equalTo(array('some query')))
            ->will($this->returnValue(array('1', '2')));
        $this->storage->expects($this->never())
            ->method('getMessage')
            ->will($this->returnValue(new \stdClass()));

        $result = $this->connector->findItems('SomeFolder', $this->connector->getSearchQueryBuilder()->get());
        $this->assertCount(2, $result);
    }

    public function testFindItemsWithParentFolderAndSearchQueryGetMessages()
    {
        $this->storage->expects($this->at(0))
            ->method('selectFolder')
            ->with($this->equalTo('SomeFolder'));
        $this->storage->expects($this->at(1))
            ->method('search')
            ->with($this->equalTo(array('some query')))
            ->will($this->returnValue(array('1', '2')));
        $this->storage->expects($this->exactly(2))
            ->method('getMessage')
            ->will($this->returnValue(new \stdClass()));

        $result = $this->connector->findItems('SomeFolder', $this->connector->getSearchQueryBuilder()->get());
        $this->assertCount(2, $result);
        foreach ($result as $r) { }
    }

    public function testFindFolders()
    {
        $folder = $this->getMockBuilder('Oro\Bundle\ImapBundle\Mail\Storage\Folder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storage->expects($this->once())
            ->method('getFolders')
            ->with($this->equalTo('SomeFolder'))
            ->will($this->returnValue($folder));

        $result = $this->connector->findFolders('SomeFolder');
        $this->assertCount(0, $result);
    }

    public function testFindFolder()
    {
        $folder = $this->getMockBuilder('Oro\Bundle\ImapBundle\Mail\Storage\Folder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->storage->expects($this->once())
            ->method('getFolders')
            ->with($this->equalTo('SomeFolder'))
            ->will($this->returnValue($folder));

        $result = $this->connector->findFolder('SomeFolder');
        $this->assertTrue($folder === $result);
    }

    public function testGetItem()
    {
        $msg = new \stdClass();

        $this->storage->expects($this->at(0))
            ->method('getNumberByUniqueId')
            ->with($this->equalTo(123))
            ->will($this->returnValue(12345));
        $this->storage->expects($this->at(1))
            ->method('getMessage')
            ->with($this->equalTo(12345))
            ->will($this->returnValue($msg));

        $result = $this->connector->getItem(123);
        $this->assertTrue($msg === $result);
    }
}
