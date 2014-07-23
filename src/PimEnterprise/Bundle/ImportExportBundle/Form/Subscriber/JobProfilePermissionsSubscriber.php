<?php

namespace PimEnterprise\Bundle\ImportExportBundle\Form\Subscriber;

use Pim\Bundle\UserBundle\Entity\Repository\GroupRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use PimEnterprise\Bundle\SecurityBundle\Manager\JobProfileAccessManager;

/**
 * Subscriber to manager permissions on job profiles
 *
 * @author    Romain Monceau <romain@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
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
     * @param GroupRepository          $userGroupRepository
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
        $event->getForm()->add('permissions', 'pimee_import_export_job_profile_permissions');
    }

    /**
     * Inject existing permissions into the form
     *
     * @param FormEvent $event
     *
     * @return null
     */
    public function postSetData(FormEvent $event)
    {
        $jobInstance = $event->getData();
        if (null === $jobInstance || null === $jobInstance->getId()) {
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
            $resource = sprintf('pimee_importexport_%s_profile_edit_permissions', $jobInstance->getType());

            if ($this->securityFacade->isGranted($resource) && null !== $jobInstance->getId()) {
                $executeGroups = $form->get('permissions')->get('execute')->getData();
                $editGroups    = $form->get('permissions')->get('edit')->getData();
            } elseif (null === $jobInstance->getId()) {
                $editGroups    = $this->userGroupRepository->findAll();
                $executeGroups = $editGroups;
            } else {
                return;
            }

            $this->accessManager->setAccess($jobInstance, $executeGroups, $editGroups);
        }
    }
}
