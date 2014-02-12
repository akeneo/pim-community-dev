<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Entity;

use Akeneo\Bundle\BatchBundle\Entity\JobInstance;

/**
 * Tests related to the JobInstance
 *
 */
class JobInstanceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Job $job
     */
    protected $jobInstance;

    /**
     * @staticvar string
     */
    const CONNECTOR = 'acme_connector';
    const TYPE      = 'export';
    const ALIAS     = 'acme_job_alias';
    const JOB_NAME  = 'job_name';

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $connector = self::CONNECTOR;
        $type      = self::TYPE;
        $alias     = self::ALIAS;
        $jobName   = self::JOB_NAME;

        $this->jobInstance = new JobInstance($connector, $type, $alias, $jobName);
    }

    /**
     * Test related method
     */
    public function testGetId()
    {
        $this->assertNull($this->jobInstance->getId());
    }

    /**
     * Test getter/setter code
     */
    public function testGetSetCode()
    {
        $this->assertNull($this->jobInstance->getCode());

        $expectedCode = 'expected_code';
        $this->assertEntity($this->jobInstance->setCode($expectedCode));
        $this->assertEquals($expectedCode, $this->jobInstance->getCode());
    }

    /**
     * Test getter/setter label
     */
    public function testGetSetLabel()
    {
        $this->assertNull($this->jobInstance->getLabel());

        $expectedLabel = 'expected label';
        $this->assertEntity($this->jobInstance->setLabel($expectedLabel));
        $this->assertEquals($expectedLabel, $this->jobInstance->getLabel());
    }

    /**
     * Test related method
     */
    public function testGetConnector()
    {
        $this->assertEquals(self::CONNECTOR, $this->jobInstance->getConnector());
    }

    /**
     * Test related method
     */
    public function testGetAlias()
    {
        $this->assertEquals(self::ALIAS, $this->jobInstance->getAlias());
    }

    /**
     * Test getter/setter status
     */
    public function testGetSetStatus()
    {
        $this->assertNull($this->getStatus());

        $expectedStatus = 1;
        $this->assertEntity($this->jobInstance->setStatus($expectedStatus));
        $this->assertEquals($expectedStatus, $this->jobInstance->getStatus());
    }

    /**
     * Test getter/setter type
     */
    public function testGetSetType()
    {
        $this->assertEquals(self::TYPE, $this->jobInstance->getType());

        $expectedType = 'import';
        $this->assertEntity($this->jobInstance->setType($expectedType));
        $this->assertEquals($expectedType, $this->jobInstance->getType());
    }

    /**
     * Test getter/setter rawConfiguration
     */
    public function testGetSetRawConfiguration()
    {
        $this->assertEmpty($this->jobInstance->getRawConfiguration());

        $expectedConfiguration = array('key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3');

        $this->assertEntity($this->jobInstance->setRawConfiguration($expectedConfiguration));
        $this->assertEquals($expectedConfiguration, $this->jobInstance->getRawConfiguration());
    }

    /**
     * Test getter/setter job
     */
    public function testGetSetJob()
    {
        $this->assertEmpty($this->jobInstance->getJob());

        $expectedConfiguration = array('key1' => 'value1', 'key2' => 'value2', 'key3' => 'value3');

        $mockJob = $this->getMockBuilder('Akeneo\\Bundle\\BatchBundle\\Job\\Job')
            ->disableOriginalConstructor()
            ->getMock();

        $mockJob->expects($this->any())
            ->method('getConfiguration')
            ->will($this->returnValue($expectedConfiguration));

        $this->assertEntity($this->jobInstance->setJob($mockJob));
        $this->assertEquals($expectedConfiguration, $this->jobInstance->getRawConfiguration());
        $this->assertEquals($mockJob, $this->jobInstance->getJob());
    }

    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Akeneo\Bundle\BatchBundle\Entity\JobInstance', $entity);
    }
}
