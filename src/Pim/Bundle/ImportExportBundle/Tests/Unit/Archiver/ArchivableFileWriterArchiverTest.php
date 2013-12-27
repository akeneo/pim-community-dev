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
    protected function setUp()
    {
        $this->factory    = $this->getZipFilesystemFactoryMock();
        $this->filesystem = $this->getFilesystemMock();
        $this->archiver   = new ArchivableFileWriterArchiver($this->factory, '/tmp', $this->filesystem);
    }

    public function testIsAnArchiver()
    {
        $this->assertInstanceOf('Pim\Bundle\ImportExportBundle\Archiver\ArchiverInterface', $this->archiver);
    }

    public function testGetName()
    {
        $this->assertSame('archive', $this->archiver->getName());
    }

    public function testDoNothingIfLessThan2FilesWereWritten()
    {
        $archivableWriter = $this->getProductWriterMock('/tmp/export.csv', array(
            __DIR__.'/../../fixtures/export.csv' => 'export.csv',
        ));
        $job = $this->getJobMock(array(
            $this->getItemStepMock($archivableWriter),
        ));

        $jobExecution = $this->getJobExecutionMock(
            $this->getJobInstanceMock('import', 'product_import', $job),
            42
        );

        $filesystem = $this->getFilesystemMock();
        $this->factory
            ->expects($this->any())
            ->method('createZip')
            ->with('/tmp/import/product_import/42/archive/export.zip')
            ->will($this->returnValue($filesystem));

        $filesystem->expects($this->never())->method('write');

        $this->archiver->archive($jobExecution);
    }

    public function testArchive()
    {
        $archivableWriter = $this->getProductWriterMock('/tmp/export.csv', array(
            __DIR__.'/../../fixtures/export.csv' => 'export.csv',
            __DIR__.'/../../fixtures/files/image1.jpg' => 'files/image1.jpg',
        ));
        $job = $this->getJobMock(array(
            $this->getItemStepMock($archivableWriter),
        ));

        $jobExecution = $this->getJobExecutionMock(
            $this->getJobInstanceMock('import', 'product_import', $job),
            42
        );

        $filesystem = $this->getFilesystemMock();
        $this->factory
            ->expects($this->any())
            ->method('createZip')
            ->with('/tmp/import/product_import/42/archive/export.zip')
            ->will($this->returnValue($filesystem));

        $filesystem->expects($this->at(0))->method('write')->with('export.csv', "An exported file\n", true);
        $filesystem->expects($this->at(1))->method('write')->with('files/image1.jpg', "An exported image\n", true);

        $this->archiver->archive($jobExecution);
    }

    public function testGetArchives()
    {
        $this->filesystem
            ->expects($this->any())
            ->method('listKeys')
            ->will($this->returnValue(array('keys' => array('foo/fooFile.txt','bar/barFile.txt'))));

        $jobExecution = $this->getJobExecutionMock(
            $this->getJobInstanceMock('import', 'product_import', null),
            42
        );
        $this->assertSame(
            array(
                'fooFile.txt' => 'foo/fooFile.txt',
                'barFile.txt' => 'bar/barFile.txt'
            ),
            $this->archiver->getArchives($jobExecution)
        );
    }

    protected function getZipFilesystemFactoryMock()
    {
        return $this->getMock('Pim\Bundle\ImportExportBundle\Filesystem\ZipFilesystemFactory');
    }

    protected function getFilesystemMock()
    {
        return $this
            ->getMockBuilder('Gaufrette\Filesystem')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getJobExecutionMock($jobInstance, $id)
    {
        $jobExecution = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();

        $jobExecution->expects($this->any())
            ->method('getJobInstance')
            ->will($this->returnValue($jobInstance));

        $jobExecution->expects($this->any())
            ->method('getId')
            ->will($this->returnValue($id));

        return $jobExecution;
    }

    protected function getJobInstanceMock($type, $alias, $job)
    {
        $jobInstance = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobInstance')
            ->disableOriginalConstructor()
            ->getMock();

        $jobInstance->expects($this->any())->method('getType')->will($this->returnValue($type));
        $jobInstance->expects($this->any())->method('getAlias')->will($this->returnValue($alias));
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

    protected function getProductWriterMock($path, array $writtenFiles)
    {
        $writer = $this
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Writer\File\ProductWriter')
            ->disableOriginalConstructor()
            ->getMock();

        $writer->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue($path));

        $writer->expects($this->any())
            ->method('getWrittenFiles')
            ->will($this->returnValue($writtenFiles));

        return $writer;
    }
}
