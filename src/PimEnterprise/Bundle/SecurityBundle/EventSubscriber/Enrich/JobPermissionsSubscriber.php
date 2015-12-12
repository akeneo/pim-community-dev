<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\SecurityBundle\EventSubscriber\Enrich;

use Akeneo\Component\Batch\Model\JobInstance;
use Pim\Bundle\ImportExportBundle\Event\JobExecutionEvents;
use Pim\Bundle\ImportExportBundle\Event\JobProfileEvents;
use PimEnterprise\Bundle\SecurityBundle\Attributes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Job profile listener used to handle permissions
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class JobPermissionsSubscriber implements EventSubscriberInterface
{
    /** @var AuthorizationCheckerInterface */
    protected $authorizationChecker;

    /**
     * Constructor
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            JobProfileEvents::PRE_EDIT             => 'checkEditPermission',
            JobProfileEvents::PRE_REMOVE           => 'checkEditPermission',
            JobProfileEvents::PRE_EXECUTE          => 'checkExecutePermission',
            JobProfileEvents::PRE_SHOW             => 'checkShowPermission',
            JobExecutionEvents::PRE_SHOW           => 'checkJobExecutionPermission',
            JobExecutionEvents::PRE_DOWNLOAD_FILES => 'checkJobExecutionPermission',
            JobExecutionEvents::PRE_DOWNLOAD_LOG   => 'checkJobExecutionPermission'
        ];
    }

    /**
     * Throws an access denied exception if the user can not edit the job profile
     *
     * @param GenericEvent $event
     */
    public function checkEditPermission(GenericEvent $event)
    {
        $this->isGranted(Attributes::EDIT, $event->getSubject());
    }

    /**
     * Throws an access denied exception if the user can not execute the job profile
     *
     * @param GenericEvent $event
     */
    public function checkExecutePermission(GenericEvent $event)
    {
        $this->isGranted(Attributes::EXECUTE, $event->getSubject());
    }

    /**
     * Throws an access denied exception if the user can not execute nor edit
     *
     * @param GenericEvent $event
     *
     * @throws AccessDeniedException
     */
    public function checkShowPermission(GenericEvent $event)
    {
        $jobInstance = $event->getSubject();
        if (false === $this->authorizationChecker->isGranted(Attributes::EDIT, $jobInstance)
            && false === $this->authorizationChecker->isGranted(Attributes::EXECUTE, $jobInstance)) {
            throw new AccessDeniedException();
        }
    }

    /**
     * Throws an access denied exception if the user can not execute the job profile
     *
     * @param GenericEvent $event
     */
    public function checkJobExecutionPermission(GenericEvent $event)
    {
        $this->isGranted(Attributes::EXECUTE, $event->getSubject()->getJobInstance());
    }

    /**
     * Throws an access denied exception if the user has not the asked permission
     *
     * @param string      $permission
     * @param JobInstance $jobInstance
     *
     * @throws AccessDeniedException
     */
    protected function isGranted($permission, JobInstance $jobInstance)
    {
        if (false === $this->authorizationChecker->isGranted($permission, $jobInstance)) {
            throw new AccessDeniedException();
        }
    }
}
