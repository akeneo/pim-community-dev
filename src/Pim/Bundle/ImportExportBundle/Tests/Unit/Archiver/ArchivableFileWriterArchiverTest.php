<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Archiver;

use Pim\Bundle\ImportExportBundle\Archiver\ArchivableFileWriterArchiver;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class ArchivableFileWriterArchiverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->filesystem = $this->getFilesystemMock();
        $this->archiver   = new ArchivableFileWriterArchiver($this->filesystem);
    }

    public function testDoNothingIfLessThan2FilesWereWritten()
    {
        $archivableWriter = $this->getArchivableFileWriterMock(array(
            __DIR__.'/../../fixtures/export.csv' => 'export.csv',
        ));
        $job = $this->getJobMock(array(
            $this->getItemStepMock($archivableWriter),
        ));

        $jobExecution = $this->getJobExecutionMock(
            $this->getJobInstanceMock('import', 'product_import', 42, $job)
        );

        $this->filesystem->expects($this->never())->method('write');

        $this->archiver->archive($jobExecution);
    }

    public function testArchive()
    {
        $archivableWriter = $this->getArchivableFileWriterMock(array(
            __DIR__.'/../../fixtures/export.csv' => 'export.csv',
            __DIR__.'/../../fixtures/files/image1.jpg' => 'files/image1.jpg',
        ));
        $job = $this->getJobMock(array(
            $this->getItemStepMock($archivableWriter),
        ));

        $jobExecution = $this->getJobExecutionMock(
            $this->getJobInstanceMock('import', 'product_import', 42, $job)
        );

        $this->filesystem->expects($this->at(0))->method('write')->with('export.csv', "An exported file\n", true);
        $this->filesystem->expects($this->at(1))->method('write')->with('files/image1.jpg', "An exported image\n", true);

        $this->archiver->archive($jobExecution);
    }

    protected function getFilesystemMock()
    {
        return $this
            ->getMockBuilder('Gaufrette\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getJobExecutionMock($jobInstance)
    {
        $jobExecution = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $jobExecution->expects($this->any())
            ->method('getJobInstance')
            ->will($this->returnValue($jobInstance));

        return $jobExecution;
    }

    protected function getJobInstanceMock($type, $alias, $id, $job)
    {
        $jobInstance = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobInstance')
            ->disableOriginalConstructor()
            ->getMock();

        $jobInstance->expects($this->any())->method('getType')->will($this->returnValue($type));
        $jobInstance->expects($this->any())->method('getAlias')->will($this->returnValue($alias));
        $jobInstance->expects($this->any())->method('getId')->will($this->returnValue($id));
        $jobInstance->expects($this->any())->method('getJob')->will($this->returnValue($job));

        return $jobInstance;
    }

    protected function getJobMock(array $steps)
    {
        $job = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Job\Job')
            ->disableOriginalConstructor()
            ->getMock();

        $job->expects($this->any())->method('getSteps')->will($this->returnValue($steps));

        return $job;
    }

    protected function getStepMock()
    {
        return $this->getMock('Oro\Bundle\BatchBundle\Step\StepInterface');
    }

    protected function getItemStepMock($writer)
    {
        $step = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Step\ItemStep')
            ->disableOriginalConstructor()
            ->getMock();

        $step->expects($this->any())->method('getWriter')->will($this->returnValue($writer));

        return $step;
    }

    protected function getArchivableFileWriterMock(array $writtenFiles)
    {
        $writer = $this->getMock('Pim\Bundle\ImportExportBundle\Writer\File\ArchivableWriterInterface');

        $writer->expects($this->any())
            ->method('getWrittenFiles')
            ->will($this->returnValue($writtenFiles));

        return $writer;
    }
}
