<?php

namespace PimEnterprise\Bundle\ImportExportBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Pim\Bundle\UserBundle\Entity\Repository\RoleRepository;
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

    /** @var RoleRepository */
    protected $roleRepository;

    /**
     * @param JobProfileAccessManager $accessManager
     * @param SecurityFacade          $securityFacade
     * @param RoleRepository          $roleRepository
     */
    public function __construct(
        JobProfileAccessManager $accessManager,
        SecurityFacade $securityFacade,
        RoleRepository $roleRepository
    ) {
        $this->accessManager  = $accessManager;
        $this->securityFacade = $securityFacade;
        $this->roleRepository = $roleRepository;
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

        $executeRoles = $this->accessManager->getExecuteRoles($jobInstance);
        $editRoles    = $this->accessManager->getEditRoles($jobInstance);

        $form = $event->getForm()->get('permissions');
        $form->get('execute')->setData($executeRoles);
        $form->get('edit')->setData($editRoles);
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
                $executeRoles = $form->get('permissions')->get('execute')->getData();
                $editRoles    = $form->get('permissions')->get('edit')->getData();
            } elseif (null === $jobInstance->getId()) {
                $editRoles    = $this->roleRepository->findAll();
                $executeRoles = $editRoles;
            } else {
                return;
            }

            $this->accessManager->setAccess($jobInstance, $executeRoles, $editRoles);
        }
    }
}
