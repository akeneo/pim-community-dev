<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\AttributeGroupAccessManager;

/**
 * Subscriber to manage permissions on attribute groups
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class AttributeGroupPermissionsSubscriber implements EventSubscriberInterface
{
    /**
     * @var AttributeGroupAccessManager
     */
    protected $accessManager;

    /**
     * @param AttributeGroupAccessManager $accessManager
     */
    public function __construct(AttributeGroupAccessManager $accessManager)
    {
        $this->accessManager = $accessManager;
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
        $event->getForm()->add('permissions', 'pimee_enrich_attribute_group_permissions');
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
        $form->get('view')->setData($this->accessManager->getViewRoles($event->getData()));
        $form->get('edit')->setData($this->accessManager->getEditRoles($event->getData()));
    }

    /**
     * Persist the permissions defined in the form
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if ($form->isValid()) {
            $viewRoles = $form->get('permissions')->get('view')->getData();
            $editRoles = $form->get('permissions')->get('edit')->getData();
            $this->accessManager->setAccess($event->getData(), $viewRoles, $editRoles);
        }
    }
}
