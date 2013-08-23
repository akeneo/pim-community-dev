<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Job;

use Pim\Bundle\BatchBundle\Job\Job;
use Pim\Bundle\BatchBundle\Job\JobInterruptedException;
use Pim\Bundle\BatchBundle\Job\BatchStatus;
use Pim\Bundle\BatchBundle\Job\ExitStatus;

/**
 * Tests related to the AbstractStep class
 *
 * @author    Benoit Jacquemont <benoit@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
class AbstractStepTest extends \PHPUnit_Framework_TestCase
{
    protected $step            = null;
    protected $eventDispatcher = null;
    protected $jobRepository   = null;

    const STEP_NAME = 'test_step_name';

    protected function setUp()
    {
        $this->eventDispatcher = $this->getMock('Symfony\\Component\\EventDispatcher\\EventDispatcherInterface');
        $this->jobRepository   = $this->getMock('Pim\\Bundle\\BatchBundle\\Job\\JobRepositoryInterface');

        $this->step = $this->getMockForAbstractClass(
            'Pim\\Bundle\\BatchBundle\\Step\\AbstractStep',
            array(self::STEP_NAME)
        );

        $this->step->setEventDispatcher($this->eventDispatcher);
        $this->step->setJobRepository($this->jobRepository);
    }

    public function testGetSetJobRepository()
    {
        $this->step = $this->getMockForAbstractClass(
            'Pim\\Bundle\\BatchBundle\\Step\\AbstractStep',
            array(self::STEP_NAME)
        );

        $this->assertNull($this->step->getJobRepository());
        $this->assertEntity($this->step->setJobRepository($this->jobRepository));
        $this->assertSame($this->jobRepository, $this->step->getJobRepository());
    }

    public function testGetSetName()
    {
        $this->assertEquals(self::STEP_NAME, $this->step->getName());
        $this->assertEntity($this->step->setName('other_name'));
        $this->assertEquals('other_name', $this->step->getName());
    }

    public function testExecute()
    {
        $stepExecution = $this->getMockBuilder('Pim\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $stepExecution->expects($this->once())
            ->method('getExitStatus')
            ->will($this->returnValue(new ExitStatus(ExitStatus::COMPLETED)));

        $stepExecution->expects($this->once())
            ->method('setEndTime')
            ->with($this->isInstanceOf('DateTime'));

        $stepExecution->expects($this->once())
            ->method('setExitStatus')
            ->with($this->equalTo(new ExitStatus(ExitStatus::COMPLETED)));

        $this->step->execute($stepExecution);
    }

    public function testExecuteWithTerminate()
    {
        $stepExecution = $this->getMockBuilder('Pim\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $stepExecution->expects($this->once())
            ->method('getExitStatus')
            ->will($this->returnValue(new ExitStatus(ExitStatus::COMPLETED)));

        $stepExecution->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::STOPPED)));

        $stepExecution->expects($this->any())
            ->method('isTerminateOnly')
            ->will($this->returnValue(true));

        $stepExecution->expects($this->once())
            ->method('upgradeStatus')
            ->with($this->equalTo(BatchStatus::STOPPED));

        $stepExecution->expects($this->once())
            ->method('setExitStatus')
            ->with(
                $this->equalTo(
                    new ExitStatus(
                        ExitStatus::STOPPED,
                        'Pim\\Bundle\\BatchBundle\\Job\\JobInterruptedException'
                    )
                )
            );

        $this->step->execute($stepExecution);
    }

    public function testExecuteWithError()
    {
        $exception = new \Exception('My exception');

        $this->step->expects($this->once())
            ->method('doExecute')
            ->will($this->throwException($exception));

        $stepExecution = $this->getMockBuilder('Pim\\Bundle\\BatchBundle\\Entity\\StepExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $stepExecution->expects($this->any())
            ->method('getStatus')
            ->will($this->returnValue(new BatchStatus(BatchStatus::FAILED)));

        $stepExecution->expects($this->once())
            ->method('upgradeStatus')
            ->with($this->equalTo(BatchStatus::FAILED));

        $stepExecution->expects($this->once())
            ->method('setExitStatus')
            ->with($this->equalTo(new ExitStatus(ExitStatus::FAILED, $exception->getTraceAsString())));

        $this->step->execute($stepExecution);
    }

    /**
     * Assert the entity tested
     *
     * @param object $entity
     */
    protected function assertEntity($entity)
    {
        $this->assertInstanceOf('Pim\Bundle\BatchBundle\Step\AbstractStep', $entity);
    }
}
