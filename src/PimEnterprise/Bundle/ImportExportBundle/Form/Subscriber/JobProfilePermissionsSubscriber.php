<?php

namespace PimEnterprise\Bundle\ImportExportBundle\Form\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Security\Core\SecurityContextInterface;
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

    /** @var SecurityContextInterface */
    protected $securityContext;

    /**
     * @param JobProfileAccessManager  $accessManager
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(JobProfileAccessManager $accessManager, SecurityContextInterface $securityContext)
    {
        $this->accessManager   = $accessManager;
        $this->securityContext = $securityContext;
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
        $resource = sprintf('pimee_importexport_%s_profile_edit_permissions', $data->getType());

        if ($form->isValid() && $this->securityContext->isGranted($resource)) {
            $executeRoles = $form->get('permissions')->get('execute')->getData();
            $editRoles    = $form->get('permissions')->get('edit')->getData();

            $this->accessManager->setAccess($event->getData(), $executeRoles, $editRoles);
        }
    }
}
