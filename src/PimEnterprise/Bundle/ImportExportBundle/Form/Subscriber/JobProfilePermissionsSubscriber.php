<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\ImportExportBundle\Form\Subscriber;

use Akeneo\Component\Batch\Model\JobInstance;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\UserBundle\Entity\Repository\GroupRepository;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber to manager permissions on job profiles
 *
 * @author Romain Monceau <romain@akeneo.com>
 */
class JobProfilePermissionsSubscriber implements EventSubscriberInterface
{
    /** @var JobProfileAccessManager */
    protected $accessManager;

    /** @var SecurityFacade */
    protected $securityFacade;

    /** @var GroupRepository */
    protected $userGroupRepository;

    /**
     * @param JobProfileAccessManager $accessManager
     * @param SecurityFacade          $securityFacade
     * @param GroupRepository         $userGroupRepository
     */
    public function __construct(
        JobProfileAccessManager $accessManager,
        SecurityFacade $securityFacade,
        GroupRepository $userGroupRepository
    ) {
        $this->accessManager  = $accessManager;
        $this->securityFacade = $securityFacade;
        $this->userGroupRepository = $userGroupRepository;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::POST_SUBMIT   => 'postSubmit'
        ];
    }

    /**
     * Add the permissions subform to the form
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        if (null !== $event->getData() && $this->isGranted($event->getData())) {
            $event->getForm()->add('permissions', 'pimee_import_export_job_profile_permissions');
        }
    }

    /**
     * Inject existing permissions into the form
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        $jobInstance = $event->getData();
        if (null === $jobInstance || null === $jobInstance->getId() || !$this->isGranted($jobInstance)) {
            return;
        }

        $executeGroups = $this->accessManager->getExecuteUserGroups($jobInstance);
        $editGroups    = $this->accessManager->getEditUserGroups($jobInstance);

        $form = $event->getForm()->get('permissions');
        $form->get('execute')->setData($executeGroups);
        $form->get('edit')->setData($editGroups);
    }

    /**
     * Persist the permissions defined in the form
     *
     * Permissions are updated only if user has rights in edit mode
     * When user creates a job instance, all user are automatically added
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $jobInstance = $event->getData();
        if (null === $jobInstance) {
            return;
        }

        $form = $event->getForm();
        if ($form->isValid()) {
            if (null === $jobInstance->getId()) {
                $editGroups    = $this->userGroupRepository->findAll();
                $executeGroups = $editGroups;
            } elseif ($this->isGranted($jobInstance)) {
                $executeGroups = $form->get('permissions')->get('execute')->getData();
                $editGroups    = $form->get('permissions')->get('edit')->getData();
            } else {
                return;
            }

            $this->accessManager->setAccess($jobInstance, $executeGroups, $editGroups);
        }
    }

    /**
     * Indicates whether the user has the rights to edit the permissions of this job instance type
     *
     * @param JobInstance $jobInstance
     *
     * @return bool
     */
    protected function isGranted(JobInstance $jobInstance)
    {
        $resource = sprintf('pimee_importexport_%s_profile_edit_permissions', $jobInstance->getType());

        return $this->securityFacade->isGranted($resource);
    }
}
