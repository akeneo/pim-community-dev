<?php

namespace Pim\Bundle\BatchBundle\Tests\Unit\Connector;

use Pim\Bundle\BatchBundle\Connector\ConnectorRegistry;
use Pim\Bundle\BatchBundle\Job\AbstractJob;

class ConnectorRegistryTest extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {
        $this->registry = new ConnectorRegistry();
    }

    public function testAddImportJobToConnector()
    {
        $job = $this->getJobMock();
        $this->registry->addJobToConnector('Akeneo', AbstractJob::TYPE_IMPORT, 'import_stuff', $job);

        $this->assertEquals(array('Akeneo' => array('import_stuff' => $job)), $this->registry->getImportJobs());
    }

    public function testAddExportJobToConnector()
    {
        $job = $this->getJobMock();
        $this->registry->addJobToConnector('Akeneo', AbstractJob::TYPE_EXPORT, 'export_stuff', $job);

        $this->assertEquals(array('Akeneo' => array('export_stuff' => $job)), $this->registry->getExportJobs());
    }

    public function testGetJob()
    {
        $job = $this->getJobMock();
        $this->registry->addJobToConnector('Akeneo', AbstractJob::TYPE_EXPORT, 'export_stuff', $job);

        $this->assertEquals($job, $this->registry->getJob('Akeneo', AbstractJob::TYPE_EXPORT, 'export_stuff'));
    }

    public function testGetUnknownJob()
    {
        $this->assertNull($this->registry->getJob('Akeneo', AbstractJob::TYPE_EXPORT, 'export_stuff'));
    }

    private function getJobMock()
    {
        return $this->getMock('Pim\\Bundle\\BatchBundle\\Job\\SimpleJob');
    }
}
