<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Entity;

use Pim\Bundle\BatchBundle\Entity\RawConfiguration;
use Pim\Bundle\BatchBundle\Entity\Connector;
use Pim\Bundle\BatchBundle\Entity\Job;

/**
 * Test related class
 *
 *
 */
class ConnectorTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Connector
     */
    protected $connector;

    /**
     * Setup
     */
    public function setup()
    {
        $this->connector = new Connector();
    }

    /**
     * Test related methods
     */
    public function testGettersSetters()
    {
        $this->assertNull($this->connector->getId());
        $this->assertNull($this->connector->getServiceId());
        $this->assertNull($this->connector->getRawConfiguration());

        $this->connector->setServiceId('my.connector.id');
        $this->connector->setId(1);
        $this->connector->setDescription('my description');
        $configuration = new RawConfiguration();
        $this->connector->setRawConfiguration($configuration);

        $this->assertEquals($this->connector->getServiceId(), 'my.connector.id');
        $this->assertEquals($this->connector->getRawConfiguration(), $configuration);
        $this->assertEquals($this->connector->getDescription(), 'my description');
    }

    /**
     * Test related methods
     */
    public function testJobs()
    {
        $this->assertEquals($this->connector->getJobs()->count(), 0);
        $job1 = new Job();
        $this->connector->addJob($job1);
        $this->assertEquals($this->connector->getJobs()->count(), 1);
        $this->assertEquals($this->connector->getJobs()->first(), $job1);
        $job2 = new Job();
        $this->connector->addJob($job2);
        $this->assertEquals($this->connector->getJobs()->count(), 2);
        $this->connector->removeJob($job1);
        $this->assertEquals($this->connector->getJobs()->count(), 1);
        $this->assertEquals($this->connector->getJobs()->first(), $job2);
    }
}
