<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\DataFlowBundle\DependencyInjection\Compiler\ConnectorRegistry;
use Oro\Bundle\DataFlowBundle\Tests\Unit\Connector\Demo\MyConnector;
use Oro\Bundle\DataFlowBundle\Tests\Unit\Job\Demo\MyJob;

/**
 * Test related class
 *
 *
 */
class ConnectorRegistryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var ConnectorRegistry
     */
    protected $registry;

    /**
     * @var string
     */
    protected $connectorId;

    /**
     * @var string
     */
    protected $jobId;

    /**
     * Setup
     */
    public function setup()
    {
        $this->registry = new ConnectorRegistry();
        $this->connectorId = 'my-con-id';
        $this->jobId = 'my-job-id';
    }

    /**
     * Test related method
     */
    public function testAddJobToConnector()
    {
        $confName = 'Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration\Demo\MyConfiguration';
        $connector = new MyConnector($confName);
        $job = new MyJob($confName, $confName);
        $this->assertEquals(count($this->registry->getConnectors()), 0);
        $this->assertEquals(count($this->registry->getJobs()), 0);
        $this->registry->addJobToConnector($this->connectorId, $connector, $this->jobId, $job);
        $this->assertEquals(count($this->registry->getConnectors()), 1);
        $this->assertEquals(count($this->registry->getJobs()), 1);
        $this->assertEquals(count($this->registry->getConnectorToJobs()), 1);
        $this->assertEquals(current(array_keys($this->registry->getConnectorToJobs())), $this->connectorId);
        $this->assertEquals(count(current($this->registry->getConnectorToJobs())), 1);
        $this->assertEquals(current(array_values(current($this->registry->getConnectorToJobs()))), $this->jobId);
    }
}
