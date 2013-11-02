<?php

namespace Pim\Bundle\ImportExportBundle\Tests\Unit\EventListener;

use Oro\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\ImportExportBundle\EventListener\ArchiveSubscriber;

/**
 * Test related class
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Test extends \PHPUnit_Framework_TestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->archiver   = $this->getArchiverMock();
        $this->subscriber = new ArchiveSubscriber($this->archiver);
    }

    /**
     * Test related method
     */
    public function testSubscribedEvents()
    {
        $this->assertEquals(
            array(
                EventInterface::AFTER_JOB_EXECUTION => 'afterJobExecution'
            ),
            ArchiveSubscriber::getSubscribedEvents()
        );
    }

    /**
     * Test related method
     */
    public function testArchiveJobExecution()
    {
        $jobExecution = $this->getJobExecutionMock();
        $event = $this->getJobExecutionEventMock($jobExecution);

        $this->archiver
            ->expects($this->once())
            ->method('archive')
            ->with($jobExecution);

        $this->subscriber->afterJobExecution($event);
    }

    /**
     * @return \Pim\Bundle\ImportExportBundle\Archiver\JobExecutionArchiver
     */
    protected function getArchiverMock()
    {
        return $this
            ->getMockBuilder('Pim\Bundle\ImportExportBundle\Archiver\JobExecutionArchiver')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \Oro\Bundle\BatchBundle\Entity\JobExecution
     */
    protected function getJobExecutionMock()
    {
        return $this
            ->getMockBuilder('Oro\Bundle\BatchBundle\Entity\JobExecution')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @param \Oro\Bundle\BatchBundle\Entity\JobExecution $jobExecution
     *
     * @return \Oro\Bundle\BatchBundle\Event\JobExecutionEvent
     */
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
