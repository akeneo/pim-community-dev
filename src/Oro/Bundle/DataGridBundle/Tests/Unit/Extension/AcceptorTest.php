<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Extension;

use Oro\Bundle\DataGridBundle\Extension\Acceptor;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;

class AcceptorTest extends \PHPUnit_Framework_TestCase
{
    /** @var DatagridConfiguration */
    protected $config;

    /** @var Acceptor */
    protected $acceptor;

    public function setUp()
    {
        $this->config   = DatagridConfiguration::create([]);
        $this->acceptor = new Acceptor($this->config);
    }

    public function tearDown()
    {
        unset($this->config);
        unset($this->acceptor);
    }

    /**
     * Test addExtension and sorting y priority, test getExtensions
     */
    public function testExtension()
    {
        $extMock1 = $this->getMockForAbstractClass('Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface');
        $extMock2 = $this->getMockForAbstractClass('Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface');

        $extMock1->expects($this->any())->method('getPriority')->will($this->returnValue(-100));
        $extMock2->expects($this->any())->method('getPriority')->will($this->returnValue(250));

        $this->acceptor->addExtension($extMock1)->addExtension($extMock2);

        $results = $this->acceptor->getExtensions();

        // test correct adding
        $this->assertCount(2, $results);

        // test sorting, LINUX like priority used here
        $this->assertEquals($extMock2, $results[0]);
        $this->assertEquals($extMock1, $results[1]);

        // test cloning, for state dependent extensions (such as pager)
        $this->assertNotSame($extMock2, $results[0]);
        $this->assertNotSame($extMock1, $results[1]);
    }

    /**
     * Test methods getConfig, setConfig
     */
    public function testSetConfig()
    {
        $this->assertSame($this->config, $this->acceptor->getConfig());

        $newConfig = DatagridConfiguration::create([]);
        $this->acceptor->setConfig($newConfig);

        $this->assertSame($newConfig, $this->acceptor->getConfig());
        $this->assertNotSame($this->config, $this->acceptor->getConfig());
    }

    /**
     * Test method acceptDatasource
     */
    public function testAcceptDatasource()
    {
        $datasourceMock = $this->getMockForAbstractClass('Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface');

        $extMock = $this->getMockForAbstractClass('Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface');
        $extMock->expects($this->once())->method('visitDatasource')->with($this->config, $datasourceMock);
        $this->acceptor->addExtension($extMock);

        $this->acceptor->acceptDatasource($datasourceMock);
    }

    /**
     * Test method acceptResult
     */
    public function testAcceptResults()
    {
        $result = ResultsObject::create([]);

        $extMock = $this->getMockForAbstractClass('Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface');
        $extMock->expects($this->once())->method('visitResult')->with($this->config, $result);
        $this->acceptor->addExtension($extMock);

        $this->acceptor->acceptResult($result);
    }

    /**
     * Test method acceptMetadata
     */
    public function testAcceptMetadata()
    {
        $data = MetadataObject::create([]);

        $extMock = $this->getMockForAbstractClass('Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface');
        $extMock->expects($this->once())->method('visitMetadata')->with($this->config, $data);
        $this->acceptor->addExtension($extMock);

        $this->acceptor->acceptMetadata($data);
    }
}
