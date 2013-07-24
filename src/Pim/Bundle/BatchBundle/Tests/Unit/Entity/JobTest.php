<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Entity;

use Pim\Bundle\BatchBundle\Entity\RawConfiguration;
use Pim\Bundle\BatchBundle\Entity\Job;
use Pim\Bundle\BatchBundle\Entity\Connector;

/**
 * Test related class
 *
 * @author    Nicolas Dupont <nicolas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Job
     */
    protected $job;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->job = new Job();
    }

    /**
     * Create connector
     *
     * @return \Pim\Bundle\BatchBundle\Entity\Connector
     */
    protected function createConnector()
    {
        return new Connector();
    }

    /**
     * Create raw configuration
     *
     * @return \Pim\Bundle\BatchBundle\Entity\RawConfiguration
     */
    protected function createRawConfiguration()
    {
        return new RawConfiguration();
    }

    /**
     * Test related method
     */
    public function testConstruct()
    {
        $this->assertNull($this->job->getId());
        $this->assertNull($this->job->getServiceId());
        $this->assertNull($this->job->getRawConfiguration());
        $this->assertNull($this->job->getConnector());
        $this->assertNull($this->job->getDescription());
    }

    /**
     * Test getter/setter on service id property
     */
    public function testGetSetServiceId()
    {
        $expectedServiceId = 'my.job.id';
        $this->assertEntity($this->job->setServiceId($expectedServiceId));
        $this->assertEquals($expectedServiceId, $this->job->getServiceId());
    }

    /**
     * Test getter/setter on status
     */
    public function testGetSetStatus()
    {
        $expectedStatus = 1;
        $this->assertEntity($this->job->setStatus($expectedStatus));
        $this->assertEquals($expectedStatus);
    }

    /**
     * Assert entity
     * @param Pim\Bundle\BatchBundle\Entity\Job $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\BatchBundle\Entity\Job', $entity);
    }

    /**
     * Test related methods
     */
    public function testGettersSetters()
    {
        $configuration = $this->createRawConfiguration();
        $connector = $this->createConnector();

//         $expectedServiceId = 'my.job.id';
//         $this->assertEntity($this->job->setServiceId('my.job.id'));
        $this->assertEntity($this->job->setRawConfiguration($configuration));
        $this->assertEntity($this->job->setConnector($connector));
//         $this->assertEntity($this->job->setDescription('my job description'));

//         $this->assertEquals($this->job->getServiceId(), 'my.job.id');
        $this->assertEquals($this->job->getRawConfiguration(), $configuration);
        $this->assertEquals($this->job->getConnector(), $connector);
//         $this->assertEquals($this->job->getDescription(), 'my job description');
    }
}
