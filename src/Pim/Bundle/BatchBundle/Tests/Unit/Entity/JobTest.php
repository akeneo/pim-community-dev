<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Entity;

use Pim\Bundle\BatchBundle\Entity\Job;
use Pim\Bundle\BatchBundle\Job\Job as BatchJob;

/**
 * Test related class
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class JobTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Job $job
     */
    protected $job;

    /**
     * @staticvar string
     */
    const CONNECTOR      = 'acme_connector';
    const TYPE           = 'export';
    const ALIAS          = 'acme_job_alias';
    const JOB_DEFINITION = 'job_definition';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $connector     = 'acme_connector';
        $type          = 'export';
        $alias         = 'acme_job_alias';
        $jobDefinition = 'job_definition';

        $this->job = new Job($connector, $type, $alias, $jobDefinition);
    }

    /**
     * Test related method
     */
    public function testGetId()
    {
        $this->assertNull($this->job->getId());
    }

    /**
     * Test getter/setter code
     */
    public function testGetSetCode()
    {
        $this->assertNull($this->job->getCode());

        $expectedCode = 'expected_code';
        $this->assertEntity($this->job->setCode($expectedCode));
        $this->assertEquals($expectedCode, $this->job->getCode());
    }

    /**
     * Test getter/setter label
     */
    public function testGetSetLabel()
    {
        $this->assertNull($this->job->getLabel());

        $expectedLabel = 'expected label';
        $this->assertEntity($this->job->setLabel($expectedLabel));
        $this->assertEquals($expectedLabel, $this->job->getLabel());
    }

    /**
     * Test related method
     */
    public function testGetConnector()
    {
        $this->assertEquals(self::CONNECTOR, $this->job->getConnector());
    }

    /**
     * Test related method
     */
    public function testGetAlias()
    {
        $this->assertEquals(self::ALIAS, $this->job->getAlias());
    }

    /**
     * Test getter/setter status
     */
    public function testGetSetStatus()
    {
        $this->assertNull($this->getStatus());

        $expectedStatus = 1;
        $this->assertEntity($this->job->setStatus($expectedStatus));
        $this->assertEquals($expectedStatus, $this->job->getStatus());
    }

    /**
     * Test getter/setter type
     */
    public function testGetSetType()
    {
        $this->assertEquals(self::TYPE, $this->job->getType());

        $expectedType = 'import';
        $this->assertEntity($this->job->setType($expectedType));
        $this->assertEquals($expectedType, $this->job->getType());
    }

    /**
     * Test getter/setter rawConfiguration
     */
    public function testGetSetRawConfiguration()
    {
        $this->assertEmpty($this->job->getRawConfiguration());

        $expectedConfiguration = array('key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3');

        $this->assertEntity($this->job->setRawConfiguration($expectedConfiguration));
        $this->assertEquals($expectedConfiguration, $this->job->getRawConfiguration());
    }

    /**
     * Test getter/setter jobDefinition
     */
    public function testGetSetJobDefinition()
    {
        $this->assertEmpty($this->job->getJobDefinition());

        $expectedConfiguration = array('key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3');

        $mockBatchJob = $mock = $this->getMockBuilder('Pim\\Bundle\\BatchBundle\\Job\\Job')
            ->disableOriginalConstructor()
            ->getMock();

        $mockBatchJob->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($expectedConfiguration));

        $this->assertEntity($this->job->setJobDefinition($mockBatchJob));
        $this->assertEquals($expectedConfiguration, $this->job->getRawConfiguration());
        $this->assertEquals($mockBatchJob, $this->job->getJobDefinition());
    }

    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\BatchBundle\Entity\Job', $entity);
    }
}
