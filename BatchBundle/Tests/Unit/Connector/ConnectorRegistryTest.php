<?php

namespace Akeneo\Bundle\BatchBundle\Tests\Unit\Connector;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Akeneo\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Akeneo\Bundle\BatchBundle\Entity\JobInstance;

class ConnectorRegistryTest extends \PHPUnit_Framework_TestCase
{
    protected $jobFactory;
    protected $stepFactory;
    protected $registry;

    public function setup()
    {
        $this->jobFactory  = $this->getConstructorDisabledMock('Akeneo\Bundle\BatchBundle\Job\JobFactory');
        $this->stepFactory = $this->getConstructorDisabledMock('Akeneo\Bundle\BatchBundle\Step\StepFactory');
        $this->registry    = new ConnectorRegistry($this->jobFactory, $this->stepFactory);
    }

    public function testAddStepToInexistantJob()
    {
        $job       = $this->getJobMock();
        $step      = $this->getConstructorDisabledMock('Akeneo\Bundle\BatchBundle\Step\ItemStep');
        $reader    = $this->getConstructorDisabledMock(
            'Akeneo\Bundle\BatchBundle\Tests\Unit\Item\ItemReaderTestHelper'
        );
        $processor = $this->getConstructorDisabledMock(
            'Akeneo\Bundle\BatchBundle\Tests\Unit\Item\ItemProcessorTestHelper'
        );
        $writer    = $this->getConstructorDisabledMock(
            'Akeneo\Bundle\BatchBundle\Tests\Unit\Item\ItemWriterTestHelper'
        );

        $this->jobFactory
            ->expects($this->once())
            ->method('createJob')
            ->with('Export some stuff')
            ->will($this->returnValue($job));

        $this->stepFactory
            ->expects($this->once())
            ->method('createStep')
            ->with(
                'Export',
                'Akeneo\Bundle\BatchBundle\Step\ItemStep',
                array(
                    'reader'    => $reader,
                    'processor' => $processor,
                    'writer'    => $writer,
                ),
                array()
            )
            ->will($this->returnValue($step));

        $job->expects($this->once())
            ->method('addStep')
            ->with('Export', $step);

        $this->registry->addStepToJob(
            'Akeneo',
            JobInstance::TYPE_EXPORT,
            'export_stuff',
            'Export some stuff',
            'Export',
            'Akeneo\Bundle\BatchBundle\Step\ItemStep',
            array(
                'reader'    => $reader,
                'processor' => $processor,
                'writer'    => $writer,
            ),
            array()
        );

        $this->assertEquals(
            array(
                'Akeneo' => array(
                    'export_stuff' => $job
                )
            ),
            $this->registry->getJobs(JobInstance::TYPE_EXPORT)
        );
    }

    public function testAddStepToExistantJob()
    {
        $job       = $this->getJobMock();
        $step0     = $this->getConstructorDisabledMock('Akeneo\Bundle\BatchBundle\Step\ItemStep');
        $step1     = $this->getConstructorDisabledMock('Akeneo\Bundle\BatchBundle\Step\ItemStep');
        $reader    = $this->getConstructorDisabledMock(
            'Akeneo\Bundle\BatchBundle\Tests\Unit\Item\ItemReaderTestHelper'
        );
        $processor = $this->getConstructorDisabledMock(
            'Akeneo\Bundle\BatchBundle\Tests\Unit\Item\ItemProcessorTestHelper'
        );
        $writer    = $this->getConstructorDisabledMock(
            'Akeneo\Bundle\BatchBundle\Tests\Unit\Item\ItemWriterTestHelper'
        );

        $this->jobFactory
            ->expects($this->once())
            ->method('createJob')
            ->with('Export some stuff')
            ->will($this->returnValue($job));

        $this->stepFactory
            ->expects($this->at(0))
            ->method('createStep')
            ->will($this->returnValue($step0));

        $this->stepFactory
            ->expects($this->at(1))
            ->method('createStep')
            ->will($this->returnValue($step1));

        $job->expects($this->exactly(2))
            ->method('addStep');

        $this->registry->addStepToJob(
            'Akeneo',
            JobInstance::TYPE_EXPORT,
            'export_stuff',
            'Export some stuff',
            'Export',
            'Akeneo\Bundle\BatchBundle\Step\ItemStep',
            array(
                'reader'    => $reader,
                'processor' => $processor,
                'writer'    => $writer,
            ),
            array()
        );

        $this->registry->addStepToJob(
            'Akeneo',
            JobInstance::TYPE_EXPORT,
            'export_stuff',
            'Export some stuff',
            'Export2',
            'Akeneo\Bundle\BatchBundle\Step\ItemStep',
            array(
                'reader'    => $reader,
                'processor' => $processor,
                'writer'    => $writer,
            ),
            array()
        );

        $this->assertEquals(
            array(
                'Akeneo' => array(
                    'export_stuff' => $job
                )
            ),
            $this->registry->getJobs(JobInstance::TYPE_EXPORT)
        );
    }

    public function testGetUnknownJob()
    {
        $this->assertNull($this->registry->getJob(new JobInstance('Akeneo', JobInstance::TYPE_EXPORT, 'export_stuff')));
    }

    private function getJobMock()
    {
        $logger = new Logger('JobLogger');
        $logger->pushHandler(new TestHandler());

        return $this->getMock('Akeneo\\Bundle\\BatchBundle\\Job\\Job', array(), array('TestJob', $logger));
    }

    private function getConstructorDisabledMock($classname)
    {
        return $this
            ->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
