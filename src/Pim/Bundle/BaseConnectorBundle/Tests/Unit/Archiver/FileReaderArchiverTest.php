<?php

namespace Pim\Bundle\BaseConnectorBundle\Tests\Unit\Archiver;

use Pim\Bundle\BaseConnectorBundle\Archiver\FileReaderArchiver;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class FileReaderArchiverTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->filesystem = $this->getFilesystemMock();
        $this->archiver   = new FileReaderArchiver($this->filesystem);
    }

    public function testIsAnArchiver()
    {
        $this->assertInstanceOf('Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface', $this->archiver);
    }

    public function testGetName()
    {
        $this->assertSame('input', $this->archiver->getName());
    }

    public function testArchive()
    {
        $fileReader   = $this->getFileReaderMock(__DIR__ . '/../../fixtures/import.csv');
        $lambdaReader = $this->getReaderMock();
        $job          = $this->getJobMock(
            array(
                $this->getStepMock(),
                $this->getItemStepMock($fileReader),
                $this->getItemStepMock($lambdaReader),
            )
        );

        $jobExecution = $this->getJobExecutionMock(
            $this->getJobInstanceMock('import', 'product_import', $job),
            42
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
            ->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\JobExecution')
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
            ->getMockBuilder('Akeneo\Bundle\BatchBundle\Entity\JobInstance')
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
            ->getMockBuilder('Akeneo\Bundle\BatchBundle\Job\Job')
            ->disableOriginalConstructor()
            ->getMock();

        $job->expects($this->any())->method('getSteps')->will($this->returnValue($steps));

        return $job;
    }

    protected function getStepMock()
    {
        return $this->getMock('Akeneo\Bundle\BatchBundle\Step\StepInterface');
    }

    protected function getItemStepMock($reader)
    {
        $step = $this
            ->getMockBuilder('Akeneo\Bundle\BatchBundle\Step\ItemStep')
            ->disableOriginalConstructor()
            ->getMock();

        $step->expects($this->any())->method('getReader')->will($this->returnValue($reader));

        return $step;
    }

    protected function getFileReaderMock($filePath)
    {
        $reader = $this
            ->getMockBuilder('Pim\Bundle\BaseConnectorBundle\Reader\File\FileReader')
            ->disableOriginalConstructor()
            ->getMock();

        $reader->expects($this->any())->method('getFilePath')->will($this->returnValue($filePath));

        return $reader;
    }

    protected function getReaderMock()
    {
        return $this->getMock('Akeneo\Bundle\BatchBundle\Item\ItemReaderInterface');
    }
}
