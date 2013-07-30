<?php
namespace Oro\Bundle\DataFlowBundle\Tests\Unit\Entity;

use Oro\Bundle\DataFlowBundle\Entity\RawConfiguration;
use Oro\Bundle\DataFlowBundle\Entity\Job;
use Oro\Bundle\DataFlowBundle\Entity\Connector;

/**
 * Test related class
 *
 *
 */
class JobTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Job
     */
    protected $job;

    /**
     * Setup
     */
    public function setup()
    {
        $this->job = new Job();
    }

    /**
     * Test related methods
     */
    public function testGettersSetters()
    {
        $this->assertNull($this->job->getId());
        $this->assertNull($this->job->getServiceId());
        $this->assertNull($this->job->getRawConfiguration());
        $this->assertNull($this->job->getConnector());
        $this->assertNull($this->job->getDescription());

        $configuration = new RawConfiguration();
        $connector = new Connector();
        $this->job->setId(1);
        $this->job->setServiceId('my.job.id');
        $this->job->setRawConfiguration($configuration);
        $this->job->setConnector($connector);
        $this->job->setDescription('my job description');

        $this->assertEquals($this->job->getServiceId(), 'my.job.id');
        $this->assertEquals($this->job->getRawConfiguration(), $configuration);
        $this->assertEquals($this->job->getConnector(), $connector);
        $this->assertEquals($this->job->getDescription(), 'my job description');
    }
}
