<?php

namespace Pim\Bundle\BaseConnectorBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Akeneo\Bundle\BatchBundle\Event\JobExecutionEvent;
use Akeneo\Bundle\BatchBundle\Event\EventInterface;
use Pim\Bundle\BaseConnectorBundle\Archiver\ArchiverInterface;
use Akeneo\Bundle\BatchBundle\Entity\JobExecution;

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
            EventInterface::BEFORE_JOB_STATUS_UPGRADE => 'beforeStatusUpgrade',
        );
    }

    /**
     * Register an archiver
     *
     * @param ArchiveInterface $archiver
     *
     * @throws \InvalidArgumentException
     */
    public function registerArchiver(ArchiverInterface $archiver)
    {
        if (array_key_exists($archiver->getName(), $this->archivers)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'There is already a registered archiver named "%s": %s',
                    $archiver->getName(),
                    get_class($this->archivers[$archiver->getName()])
                )
            );
        }

        $this->archivers[$archiver->getName()] = $archiver;
    }

    /**
     * Delegate archiving to the registered archivers
     *
     * @param JobExecutionEvent $event
     */
    public function beforeStatusUpgrade(JobExecutionEvent $event)
    {
        $jobExecution = $event->getJobExecution();

        foreach ($this->archivers as $archiver) {
            $archiver->archive($jobExecution);
        }
    }

    /**
     * Get the archives generated by the archivers
     *
     * @param JobExecution $jobExecution
     *
     * @return array
     */
    public function getArchives(JobExecution $jobExecution)
    {
        $result = array();

        if (!$jobExecution->isRunning()) {
            foreach ($this->archivers as $archiver) {
                if (count($archives = $archiver->getArchives($jobExecution)) > 0) {
                    $result[$archiver->getName()] = $archives;
                }
            }
        }

        return $result;
    }

    /**
     * Get an archive of an archiver
     *
     * @param JobExecution $jobExecution
     * @param string       $archiver
     * @param string       $key
     *
     * @return \Gaufrette\Stream
     *
     * @throws \InvalidArgumentException
     */
    public function getArchive(JobExecution $jobExecution, $archiver, $key)
    {
        if (!isset($this->archivers[$archiver])) {
            throw new \InvalidArgumentException(
                sprintf('Archiver "%s" is not registered', $archiver)
            );
        }

        return $this->archivers[$archiver]->getArchive($jobExecution, $key);
    }
}
