<?php

namespace Oro\Bundle\DataGridBundle\Tests\Unit\Extension;

use Oro\Bundle\DataGridBundle\Datagrid\Common\DatagridConfiguration;
use Oro\Bundle\DataGridBundle\Datagrid\Common\MetadataIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\Common\ResultsIterableObject;
use Oro\Bundle\DataGridBundle\Datagrid\RequestParameters;
use Oro\Bundle\DataGridBundle\Datasource\DatasourceInterface;
use Oro\Bundle\DataGridBundle\Extension\ExtensionVisitorInterface;
use Oro\Bundle\DataGridBundle\Tests\Unit\DataFixtures\Stub\Extension\Configuration;
use Oro\Bundle\DataGridBundle\Tests\Unit\DataFixtures\Stub\Extension\SomeExtension;

class AbstractExtensionTest extends \PHPUnit_Framework_TestCase
{
    /** @var ExtensionVisitorInterface */
    protected $extension;

    public function setUp(): void
    {
        $this->extension = new SomeExtension();
    }

    public function tearDown()
    {
        unset($this->extension);
    }

    /**
     * Test method getRequestParams
     */
    public function testGetRequestParams()
    {
        $method = new \ReflectionMethod($this->extension, 'getRequestParams');
        $method->setAccessible(true);

        $result = $method->invoke($this->extension);
        $this->assertNull($result, 'Could be empty from constructor');

        $requestParams = $this->getMockBuilder(RequestParameters::class)
            ->disableOriginalConstructor()->getMock();
        $newExtension = new SomeExtension($requestParams);

        $result = $method->invoke($newExtension);
        $this->assertSame($requestParams, $result, 'Correct set through constructor');
    }

    public function testGetPriority()
    {
        $this->assertSame(0, $this->extension->getPriority(), 'Should be zero by default');
    }

    /**
     * Empty implementation should be callable
     */
    public function testVisitDatasource()
    {
        $datasourceMock = $this->getMockForAbstractClass(DatasourceInterface::class);
        $config = DatagridConfiguration::create([]);

        $this->extension->visitDatasource($config, $datasourceMock);
    }

    /**
     * Empty implementation should be callable
     */
    public function testVisitResult()
    {
        $result = ResultsIterableObject::create([]);
        $config = DatagridConfiguration::create([]);

        $this->extension->visitResult($config, $result);
    }

    /**
     * Empty implementation should be callable
     */
    public function testVisitMetadata()
    {
        $data = MetadataIterableObject::create([]);
        $config = DatagridConfiguration::create([]);

        $this->extension->visitMetadata($config, $data);
    }

    public function testValidateConfiguration()
    {
        $configBody = [Configuration::NODE => 'test'];
        $config = [Configuration::ROOT => $configBody];

        $method = new \ReflectionMethod($this->extension, 'validateConfiguration');
        $method->setAccessible(true);

        $result = $method->invoke($this->extension, new Configuration(), $config);
        $this->assertSame($configBody, $result);
    }
}
