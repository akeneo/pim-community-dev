<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\EventListener;

use Oro\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\ImportExportBundle\EventListener\JobExecutionArchivist;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionArchivistTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        $this->archivist = new JobExecutionArchivist();
    }

    public function testSubscribedEvents()
    {
        $this->assertEquals(
            [
                EventInterface::AFTER_JOB_EXECUTION => 'afterJobExecution'
            ],
            JobExecutionArchivist::getSubscribedEvents()
        );
    }

    public function testRegisterArchiver()
    {
        $foo = $this->getArchiverMock('foo');
        $bar = $this->getArchiverMock('bar');

        $this->archivist->registerArchiver($foo);
        $this->archivist->registerArchiver($bar);

        $this->assertAttributeEquals(['foo' => $foo, 'bar' => $bar], 'archivers', $this->archivist);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage There is already a registered archiver named "foo"
     */
    public function testRegisterArchiverWithAlreadyUsedName()
    {
        $foo = $this->getArchiverMock('foo');
        $bar = $this->getArchiverMock('foo');

        $this->archivist->registerArchiver($foo);
        $this->archivist->registerArchiver($bar);
    }

    public function testArchiveJobExecution()
    {
        // Job execution event
        $jobExecution = $this->getJobExecutionMock();
        $event = $this->getJobExecutionEventMock($jobExecution);

        // Initializing some archivers
        $foo = $this->getArchiverMock('foo');
        $bar = $this->getArchiverMock('bar');
        $this->archivist->registerArchiver($foo);
        $this->archivist->registerArchiver($bar);

        // Mocking calls
        $foo->expects($this->once())->method('archive')->with($jobExecution);
        $bar->expects($this->once())->method('archive')->with($jobExecution);

        // Running the tested method
        $this->archivist->afterJobExecution($event);
    }

    public function testGetArchives()
    {
        $foo = $this->getArchiverMock('foo', ['fooArch1', 'fooArch2']);
        $bar = $this->getArchiverMock('bar', ['barArch1', 'barArch2']);
        $this->archivist->registerArchiver($foo);
        $this->archivist->registerArchiver($bar);

        $jobExecution = $this->getJobExecutionMock();

        $this->assertSame(
            [
                'foo' => ['fooArch1', 'fooArch2'],
                'bar' => ['barArch1', 'barArch2'],
            ],
            $this->archivist->getArchives($jobExecution)
        );
    }

    public function testGetArchive()
    {
        $foo = $this->getArchiverMock('foo', [], 'fooArch1Stream');
        $this->archivist->registerArchiver($foo);

        $jobExecution = $this->getJobExecutionMock();

        $this->assertSame(
            'fooArch1Stream',
            $this->archivist->getArchive($jobExecution, 'foo', 'fooArch1')
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Archiver "bar" is not registered
     */
    public function testGetArchiveFromUnknownArchiver()
    {
        $foo = $this->getArchiverMock('foo', [], 'fooArch1Stream');
        $this->archivist->registerArchiver($foo);

        $jobExecution = $this->getJobExecutionMock();

        $this->archivist->getArchive($jobExecution, 'bar', 'barArch1');
    }

    protected function getArchiverMock($name, array $archives = [], $archive = null)
    {
        $archiver = $this->getMock('Pim\Bundle\ImportExportBundle\Archiver\ArchiverInterface');

        $archiver->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));

        $archiver->expects($this->any())
            ->method('getArchives')
            ->will($this->returnValue($archives));

        $archiver->expects($this->any())
            ->method('getArchive')
            ->will($this->returnValue($archive));

        return $archiver;
    }

    protected function getJobExecutionMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }

    protected function getJobExecutionEventMock($jobExecution)
    {
        $event = $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Event\JobExecutionEvent')
            ->disableOriginalConstructor()
            ->getMock();

        $event->expects($this->any())
            ->method('getJobExecution')
            ->will($this->returnValue($jobExecution));

        return $event;
    }
}
