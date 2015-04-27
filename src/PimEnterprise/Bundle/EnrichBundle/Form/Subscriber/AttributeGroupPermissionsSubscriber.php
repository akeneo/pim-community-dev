<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Oro\Bundle\SecurityBundle\SecurityFacade;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber to manage permissions on attribute groups
 *
 * @author Filips Alpe <filips@akeneo.com>
 */
class AttributeGroupPermissionsSubscriber implements EventSubscriberInterface
{
    /**
     * @var AttributeGroupAccessManager
     */
    protected $accessManager;

    /** @var SecurityFacade */
    protected $securityFacade;

    /**
     * @param AttributeGroupAccessManager $accessManager
     * @param SecurityFacade              $securityFacade
     */
    public function __construct(AttributeGroupAccessManager $accessManager, SecurityFacade $securityFacade)
    {
        $this->accessManager  = $accessManager;
        $this->securityFacade = $securityFacade;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA  => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::POST_SUBMIT   => 'postSubmit'
        );
    }

    /**
     * Add the permissions subform to the form
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        if (null !== $event->getData()
            && $this->securityFacade->isGranted('pimee_enrich_attribute_group_edit_permissions')
        ) {
            $event->getForm()->add('permissions', 'pimee_enrich_attribute_group_permissions');
        }
    }

    /**
     * Inject existing permissions into the form
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        if (null !== $event->getData()
            && null !== $event->getData()->getId()
            && $this->securityFacade->isGranted('pimee_enrich_attribute_group_edit_permissions')
        ) {
            $form = $event->getForm()->get('permissions');
            $form->get('view')->setData($this->accessManager->getViewUserGroups($event->getData()));
            $form->get('edit')->setData($this->accessManager->getEditUserGroups($event->getData()));
        }
    }

    /**
     * Persist the permissions defined in the form
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        if (null !== $event->getData()
            && null !== $event->getData()->getId()
            && $this->securityFacade->isGranted('pimee_enrich_attribute_group_edit_permissions')
        ) {
            $form = $event->getForm();
            if ($form->isValid()) {
                $viewRoles = $form->get('permissions')->get('view')->getData();
                $editRoles = $form->get('permissions')->get('edit')->getData();
                $this->accessManager->setAccess($event->getData(), $viewRoles, $editRoles);
            }
        }
    }
}
