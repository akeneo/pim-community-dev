<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Datagrid;

use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\Datagrid;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\Acceptor;

class DatagridTest extends \PHPUnit_Framework_TestCase
{
    const TEST_NAME = 'testName';

    /** @var Datagrid */
    protected $grid;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $acceptor;

    public function setUp(): void
    {
        $this->acceptor = $this->getMockBuilder(Acceptor::class)
            ->disableOriginalConstructor()->getMock();
        $this->grid = new Datagrid(self::TEST_NAME, $this->acceptor);
    }

    public function tearDown()
    {
        unset($this->acceptor);
        unset($this->grid);
    }

    /**
     * Test method getName
     */
    public function testGetName()
    {
        $this->assertEquals(self::TEST_NAME, $this->grid->getName());
    }

    /**
     * Test methods setDatasource, getDatasource
     */
    public function testSetDatasource()
    {
        $dataSource = $this->getMockForAbstractClass(DatasourceInterface::class);

        $this->assertNull($this->grid->getDatasource());
        $this->grid->setDatasource($dataSource);

        $this->assertSame($dataSource, $this->grid->getDatasource());
    }

    /**
     * Test methods setAcceptor, getAcceptor
     */
    public function testSetAcceptor()
    {
        $anotherOneAcceptor = clone $this->acceptor;

        $this->assertSame($this->acceptor, $this->grid->getAcceptor());
        $this->assertNotSame($anotherOneAcceptor, $this->grid->getAcceptor());

        $this->grid->setAcceptor($anotherOneAcceptor);

        $this->assertSame($anotherOneAcceptor, $this->grid->getAcceptor());
        $this->assertNotSame($this->acceptor, $this->grid->getAcceptor());
    }

    /**
     * Test method getData
     */
    public function testGetData()
    {
        $dataSource = $this->getMockForAbstractClass(DatasourceInterface::class);
        $this->grid->setDatasource($dataSource);

        $resultFQCN = ResultsIterableObject::class;

        $this->acceptor->expects($this->once())->method('acceptDatasource')
            ->with($dataSource);
        $this->acceptor->expects($this->once())->method('acceptResult')
            ->with($this->isInstanceOf($resultFQCN));

        $result = $this->grid->getData();
        $this->assertInstanceOf($resultFQCN, $result);
    }

    /**
     * Test method getAcceptedDataSource
     */
    public function testGetAcceptedDataSource()
    {
        $dataSource = $this->getMockForAbstractClass(DatasourceInterface::class);
        $this->grid->setDatasource($dataSource);

        $this->acceptor->expects($this->once())->method('acceptDatasource')
            ->with($dataSource);

        $result = $this->grid->getAcceptedDatasource();
        $this->assertEquals($dataSource, $result);
    }

    /**
     * Test method getMetaData
     */
    public function testGetMetaData()
    {
        $resultFQCN = MetadataIterableObject::class;

        $this->acceptor->expects($this->once())->method('acceptMetadata')
            ->with($this->isInstanceOf($resultFQCN));

        $result = $this->grid->getMetadata();
        $this->assertInstanceOf($resultFQCN, $result);
    }
}
