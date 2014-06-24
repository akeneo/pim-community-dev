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
        if (null === $event->getData() || null === $event->getData()->getId()) {
            return;
        }

        $form = $event->getForm()->get('permissions');

        $executeRoles = $this->accessManager->getExecuteRoles($event->getData());
        $editRoles    = $this->accessManager->getEditRoles($event->getData());

        $form->get('execute')->setData($executeRoles);
        $form->get('edit')->setData($editRoles);
    }

    /**
     * Persist the permissions defined in the form
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        $data = $event->getData();
        if (null === $data || null === $data->getId()) {
            return;
        }

        $resource = sprintf('pimee_importexport_%s_profile_edit_permissions', $data->getType());

        if ($form->isValid() && $this->securityFacade->isGranted($resource)) {
            $executeRoles = $form->get('permissions')->get('execute')->getData();
            $editRoles    = $form->get('permissions')->get('edit')->getData();

            $this->accessManager->setAccess($event->getData(), $executeRoles, $editRoles);
        }
    }
}
