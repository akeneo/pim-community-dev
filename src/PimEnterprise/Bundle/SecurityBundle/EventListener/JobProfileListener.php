<?php

namespace PimEnterprise\Bundle\SecurityBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\ImportExportBundle\JobEvents;
use PimEnterprise\Bundle\SecurityBundle\SecurityContext;
use PimEnterprise\Bundle\SecurityBundle\Voter\JobProfileVoter;

/**
 * Job profile listener used to handle permissions
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class JobProfileListener implements EventSubscriberInterface
{
    /** @var SecurityContext */
    protected $securityContext;

    /**
     * Constructor
     *
     * @param SecurityFacade $securityContext
     */
    public function __construct(SecurityContext $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            JobEvents::PRE_EDIT_JOB_PROFILE    => 'checkEditPermission',
            JobEvents::PRE_EXECUTE_JOB_PROFILE => 'checkExecutePermission'
        ];
    }

    /**
     * Throws an access denied exception if the user can not edit the job profile
     *
     * @param GenericEvent $event
     *
     * @throws AccessDeniedException
     */
    public function checkEditPermission(GenericEvent $event)
    {
        $resource = sprintf('pim_importexport_%s_profile_edit', $event->getSubject()->getType());

        if (false === $this->securityContext->isGranted(JobProfileVoter::EDIT_JOB_PROFILE, $event->getSubject())) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Throws an access denied exception if the user can not execute the job profile
     *
     * @param GenericEvent $event
     *
     * @throws AccessDeniedException
     */
    public function checkExecutePermission(GenericEvent $event)
    {
        if (false === $this->securityContext->isGranted(JobProfileVoter::EXECUTE_JOB_PROFILE, $event->getSubject())) {
            throw new AccessDeniedException();
        }
    }
}
