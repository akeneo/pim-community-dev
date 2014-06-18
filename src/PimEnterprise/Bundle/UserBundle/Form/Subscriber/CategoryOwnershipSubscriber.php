<?php

namespace PimEnterprise\Bundle\UserBundle\Form\Subscriber;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryOwnershipManager;

/**
 * Subscriber to manage category ownership for roles
 *
 * @author    Filips Alpe <filips@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryOwnershipSubscriber implements EventSubscriberInterface
{
    /**
     * @var CategoryOwnershipManager
     */
    protected $ownershipManager;

    /**
     * @param CategoryOwnershipManager $ownershipManager
     */
    public function __construct(CategoryOwnershipManager $ownershipManager)
    {
        $this->ownershipManager = $ownershipManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_SUBMIT  => 'postSubmit'
        ];
    }

    /**
     * Add the ownership subform to the form
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $event->getForm()->add('ownership', 'pimee_user_category_ownership');
    }

    /**
     * Persist the ownership defined in the form
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        $form = $event->getForm();
        if ($form->isValid()) {
            $subForm = $form->get('ownership');
            $role = $event->getData();

            foreach ($subForm->get('appendCategories')->getData() as $grantedCategory) {
                $this->ownershipManager->grantOwnership($role, $grantedCategory);
            }

            foreach ($subForm->get('removeCategories')->getData() as $revokedCategory) {
                $this->ownershipManager->revokeOwnership($role, $revokedCategory);
            }
        }
    }
}
