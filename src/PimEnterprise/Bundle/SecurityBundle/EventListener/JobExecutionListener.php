<?php

namespace PimEnterprise\Bundle\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\SecurityContextInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

use Pim\Bundle\ImportExportBundle\JobEvents;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

/**
 * Job execution listener used to handle permissions
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobExecutionListener implements EventSubscriberInterface
{
    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(SecurityContextInterface $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            JobEvents::PRE_SHOW_JOB_EXECUTION     => 'checkPermissions',
            JobEvents::PRE_DL_FILES_JOB_EXECUTION => 'checkPermissions',
            JobEvents::PRE_DL_LOG_JOB_EXECUTION   => 'checkPermissions'
        ];
    }

    /**
     * Throws an access denied exception if the user can not execute the job profile
     *
     * @param GenericEvent $event
     *
     * @throws AccessDeniedException
     */
    protected function checkPermissions(GenericEvent $event)
    {
        $jobInstance = $event->getSubsject()->getJobInstance();
        if (false === $this->securityContext->isGranted(JobProfileVoter::EXECUTE_JOB_PROFILE, $jobInstance)) {
            throw new AccessDeniedException();
        }
    }
}
