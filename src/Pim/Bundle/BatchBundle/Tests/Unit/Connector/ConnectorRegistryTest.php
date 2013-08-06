<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Connector;

use Monolog\Logger;
use Monolog\Handler\TestHandler;
use Pim\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Pim\Bundle\BatchBundle\Entity\Job;

class ConnectorRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->jobFactory  = $this->getConstructorDisabledMock('Pim\Bundle\BatchBundle\Job\JobFactory');
        $this->stepFactory = $this->getConstructorDisabledMock('Pim\Bundle\BatchBundle\Step\StepFactory');
        $this->registry    = new ConnectorRegistry($this->jobFactory, $this->stepFactory);
    }

    public function testAddStepToInexistantJob()
    {
        $job       = $this->getJobMock();
        $step      = $this->getConstructorDisabledMock('Pim\Bundle\BatchBundle\Step\ItemStep');
        $reader    = $this->getConstructorDisabledMock('Pim\Bundle\ImportExportBundle\Reader\ProductReader');
        $processor = $this->getConstructorDisabledMock(
            'Pim\Bundle\ImportExportBundle\Processor\CsvSerializerProcessor'
        );
        $writer    = $this->getConstructorDisabledMock('Pim\Bundle\ImportExportBundle\Writer\FileWriter');

        $this->jobFactory
            ->expects($this->once())
            ->method('createJob')
            ->with('Export some stuff')
            ->will($this->returnValue($job));

        $this->stepFactory
            ->expects($this->once())
            ->method('createStep')
            ->with('Export', $reader, $processor, $writer)
            ->will($this->returnValue($step));

        $job->expects($this->once())
            ->method('addStep')
            ->with($step);

        $this->registry->addStepToJob(
            'Akeneo',
            Job::TYPE_EXPORT,
            'export_stuff',
            'Export some stuff',
            'Export',
            $reader,
            $processor,
            $writer
        );

        $this->assertEquals(
            array(
                'Akeneo' => array(
                    'export_stuff' => $job
                )
            ),
            $this->registry->getJobs(Job::TYPE_EXPORT)
        );
    }

    public function testAddStepToExistantJob()
    {
        $job       = $this->getJobMock();
        $step0     = $this->getConstructorDisabledMock('Pim\Bundle\BatchBundle\Step\ItemStep');
        $step1     = $this->getConstructorDisabledMock('Pim\Bundle\BatchBundle\Step\ItemStep');
        $reader    = $this->getConstructorDisabledMock('Pim\Bundle\ImportExportBundle\Reader\ProductReader');
        $processor = $this->getConstructorDisabledMock(
            'Pim\Bundle\ImportExportBundle\Processor\CsvSerializerProcessor'
        );
        $writer    = $this->getConstructorDisabledMock('Pim\Bundle\ImportExportBundle\Writer\FileWriter');

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
            Job::TYPE_EXPORT,
            'export_stuff',
            'Export some stuff',
            'Export',
            $reader,
            $processor,
            $writer
        );

        $this->registry->addStepToJob(
            'Akeneo',
            Job::TYPE_EXPORT,
            'export_stuff',
            'Export some stuff',
            'Export2',
            $reader,
            $processor,
            $writer
        );

        $this->assertEquals(
            array(
                'Akeneo' => array(
                    'export_stuff' => $job
                )
            ),
            $this->registry->getJobs(Job::TYPE_EXPORT)
        );
    }

    public function testGetUnknownJob()
    {
        $this->assertNull($this->registry->getJob(new Job('Akeneo', Job::TYPE_EXPORT, 'export_stuff')));
    }

    private function getJobMock()
    {
        $logger = new Logger('JobLogger');
        $logger->pushHandler(new TestHandler());
        return $this->getMock('Pim\\Bundle\\BatchBundle\\Job\\Job', array(), array('TestJob', $logger));
    }

    private function getConstructorDisabledMock($classname)
    {
        return $this
            ->getMockBuilder($classname)
            ->disableOriginalConstructor()
            ->getMock();
    }
}
