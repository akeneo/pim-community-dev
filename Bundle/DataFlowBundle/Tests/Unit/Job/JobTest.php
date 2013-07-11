<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\Job;

use Oro\Bundle\DataFlowBundle\Tests\Unit\Job\Demo\MyJob;
use Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration\Demo\MyConfiguration;
use Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration\Demo\MyOtherConfiguration;

/**
 * Test related class
 *
 *
 */
class JobTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MyJob
     */
    protected $job;

    /**
     * @var string
     */
    protected $conConfName;

    /**
     * @var string
     */
    protected $jobConfName;

    /**
     * Setup
     */
    public function setup()
    {
        $this->conConfName = 'Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration\Demo\MyConfiguration';
        $this->jobConfName = 'Oro\Bundle\DataFlowBundle\Tests\Unit\Configuration\Demo\MyConfiguration';
        $this->job = new MyJob($this->conConfName, $this->jobConfName);
    }

    /**
     * Test related method
     */
    public function testConfigure()
    {
        $this->assertNull($this->job->getConfiguration());
        $this->assertNull($this->job->getConnectorConfiguration());
        $this->assertEquals($this->job->getConfigurationName(), $this->jobConfName);
        $this->assertEquals($this->job->getConnectorConfigurationName(), $this->conConfName);
        $conConfiguration = new MyConfiguration();
        $jobConfiguration = new MyConfiguration();
        $this->job->configure($conConfiguration, $jobConfiguration);
        $this->assertEquals($this->job->getConfiguration(), $jobConfiguration);
        $this->assertEquals($this->job->getConnectorConfiguration(), $conConfiguration);
        $this->assertEquals($this->job->getConfigurationName(), $this->jobConfName);
        $this->assertEquals($this->job->getConnectorConfigurationName(), $this->conConfName);
    }

    /**
     * Test related method
     * @expectedException \Oro\Bundle\DataFlowBundle\Exception\ConfigurationException
     */
    public function testConfigureConnectorException()
    {
        $conConfiguration = new MyConfiguration();
        $jobConfiguration = new MyOtherConfiguration();

        $this->job->configure($conConfiguration, $jobConfiguration);
    }

    /**
     * Test related method
     * @expectedException \Oro\Bundle\DataFlowBundle\Exception\ConfigurationException
     */
    public function testConfigureJobException()
    {
        $conConfiguration = new MyOtherConfiguration();
        $jobConfiguration = new MyConfiguration();

        $this->job->configure($conConfiguration, $jobConfiguration);
    }

    /**
     * Test related method
     */
    public function testRun()
    {
        $this->job->run();
    }
}
