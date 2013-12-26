<?php

namespace Pim\Bundle\ImportExportBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Oro\Bundle\BatchBundle\Event\JobExecutionEvent;
use Oro\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\ImportExportBundle\Archiver\JobExecutionArchiver;
use Gaufrette\Filesystem;
use Pim\Bundle\ImportExportBundle\Archiver\ArchiverInterface;

/**
 * Job execution archivist
 *
 * @author    Gildas Quemener <gildas@akeneo.com>
 * @copyright 2013 Akeneo SAS (http://www.akeneo.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class JobExecutionArchivist implements EventSubscriberInterface
{
    /** @var array */
    protected $archivers = array();

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            EventInterface::AFTER_JOB_EXECUTION => 'afterJobExecution',
        );
    }

    /**
     * Register an archiver
     *
     * @param ArchiveInterface $archiver
     */
    public function registerArchiver(ArchiverInterface $archiver)
    {
        $this->archivers[] = $archiver;
    }

    /**
     * Delegate archiving to the registered archivers
     *
     * @param JobExecutionEvent $event
     */
    public function afterJobExecution(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        foreach ($this->archivers as $archiver) {
            $archiver->archive($jobExecution);
        }
    }
}
