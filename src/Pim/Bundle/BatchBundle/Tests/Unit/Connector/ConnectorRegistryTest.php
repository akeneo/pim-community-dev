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
        $this->registry = new ConnectorRegistry();
    }

    public function testAddImportJobToConnector()
    {
        $job = $this->getJobMock();
        $this->registry->addJobToConnector('Akeneo', Job::TYPE_IMPORT, 'import_stuff', $job);

        $this->assertEquals(array('Akeneo' => array('import_stuff' => $job)), $this->registry->getImportJobs());
    }

    public function testAddExportJobToConnector()
    {
        $job = $this->getJobMock();
        $this->registry->addJobToConnector('Akeneo', Job::TYPE_EXPORT, 'export_stuff', $job);

        $this->assertEquals(array('Akeneo' => array('export_stuff' => $job)), $this->registry->getExportJobs());
    }

    public function testGetJob()
    {
        $job = $this->getJobMock();
        $this->registry->addJobToConnector('Akeneo', Job::TYPE_EXPORT, 'export_stuff', $job);

        $this->assertEquals($job, $this->registry->getJob(new Job('Akeneo', Job::TYPE_EXPORT, 'export_stuff')));
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
}
