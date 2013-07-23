<?php
namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Pim\Bundle\BatchBundle\Tests\Unit\Job\Demo\MyJob;
use Pim\Bundle\BatchBundle\Tests\Unit\Configuration\Demo\MyConfiguration;
use Pim\Bundle\BatchBundle\Tests\Unit\Configuration\Demo\MyOtherConfiguration;
use Pim\Bundle\BatchBundle\Job\JobExecution;

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
        $this->conConfName = 'Pim\Bundle\BatchBundle\Tests\Unit\Configuration\Demo\MyConfiguration';
        $this->jobConfName = 'Pim\Bundle\BatchBundle\Tests\Unit\Configuration\Demo\MyConfiguration';
        $this->job = new MyJob('My job', $this->conConfName, $this->jobConfName);
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
     * @expectedException \Pim\Bundle\BatchBundle\Exception\ConfigurationException
     */
    public function testConfigureConnectorException()
    {
        $conConfiguration = new MyConfiguration();
        $jobConfiguration = new MyOtherConfiguration();

        $this->job->configure($conConfiguration, $jobConfiguration);
    }

    /**
     * Test related method
     * @expectedException \Pim\Bundle\BatchBundle\Exception\ConfigurationException
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
    public function testExecute()
    {
        $this->job->execute(new JobExecution());
    }
}
