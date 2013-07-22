<?php

namespace Oro\Bundle\NotificationBundle\Event\Handler;

use JMS\JobQueueBundle\Entity\Job;

abstract class EventHandlerAbstract implements EventHandlerInterface
{
    /**
     * Add command to job queue if it has not been added earlier
     *
     * @return boolean|integer
     */
    public function addJob($command, $commandArgs)
    {
        $currJob = $this->em
            ->createQuery("SELECT j FROM JMSJobQueueBundle:Job j WHERE j.command = :command AND j.state <> :state")
            ->setParameter('command', $command)
            ->setParameter('state', Job::STATE_FINISHED)
            ->setMaxResults(1)
            ->getOneOrNullResult();

        if (!$currJob) {
            $job = new Job($command, $commandArgs);
            $this->em->persist($job);
            $this->em->flush($job);
        }

        return $currJob ? $currJob->getId(): true;
    }
}