<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\Archiver;

use Pim\Bundle\ImportExportBundle\Archiver\FileReaderArchiver;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileReaderArchiverTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->filesystem = $this->getFilesystemMock();
        $this->archiver   = new FileReaderArchiver($this->filesystem);
    }

    public function testArchive()
    {
        $fileReader = $this->getFileReaderMock(__DIR__ . '/../../fixtures/import.csv');
        $lambdaReader = $this->getReaderMock();
        $job = $this->getJobMock(array(
            $this->getStepMock(),
            $this->getItemStepMock($fileReader),
            $this->getItemStepMock($lambdaReader),
        ));

        $jobExecution = $this->getJobExecutionMock(
            $this->getJobInstanceMock('import', 'product_import', 42, $job)
        );

        $this->filesystem
            ->expects($this->once())
            ->method('write')
            ->with(
                'import/product_import/42/input/import.csv',
                "firstname;lastname;age\nSeverin;Gero;28\nKyrylo;Zdislav;34\nCenek;Wojtek;7\n",
                true
            );

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

    protected function getItemStepMock($reader)
    {
        $step = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Step\ItemStep')
            ->disableOriginalConstructor()
            ->getMock();

        $step->expects($this->any())->method('getReader')->will($this->returnValue($reader));

        return $step;
    }

    protected function getFileReaderMock($filePath)
    {
        $reader = $this
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Reader\File\FileReader')
            ->disableOriginalConstructor()
            ->getMock();

        $reader->expects($this->any())->method('getFilePath')->will($this->returnValue($filePath));

        return $reader;
    }

    protected function getReaderMock()
    {
        return $this->getMock('Oro\Bundle\BatchBundle\Item\ItemReaderInterface');
    }
}
