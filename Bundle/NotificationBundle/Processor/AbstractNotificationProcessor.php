<?php

namespace Oro\Bundle\NotificationBundle\Processor;

use Psr\Log\LoggerInterface;
use Doctrine\ORM\EntityManager;
use JMS\JobQueueBundle\Entity\Job;

abstract class AbstractNotificationProcessor
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param EntityManager   $em
     */
    protected function __construct(LoggerInterface $logger, EntityManager $em)
    {
        $this->logger = $logger;
        $this->em     = $em;
    }

    /**
     * Add command to job queue if it has not been added earlier
     *
     * @param string $command
     * @param array $commandArgs
     * @param boolean $needFlush
     * @return boolean|integer
     */
    protected function addJob($command, $commandArgs = array(), $needFlush = false)
    {
        $currJob = $this->em
            ->createQuery("SELECT j FROM JMSJobQueueBundle:Job j WHERE j.command = :command AND j.state <> :state")
            ->setParameter('command', $command)
            ->setParameter('state', Job::STATE_FINISHED)
            ->getOneOrNullResult();

        if (!$currJob) {
            $job = new Job($command, $commandArgs);
            $this->em->persist($job);
            if ($needFlush) {
                $this->em->flush($job);
            }
        }

        return $currJob ? $currJob->getId() : true;
    }
}
