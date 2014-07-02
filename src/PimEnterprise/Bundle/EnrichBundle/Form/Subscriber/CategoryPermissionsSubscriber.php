<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscriber to manage permissions on categories
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryPermissionsSubscriber implements EventSubscriberInterface
{
    /**
     * @var CategoryAccessManager
     */
    protected $accessManager;

    /**
     * @param CategoryAccessManager $accessManager
     */
    public function __construct(CategoryAccessManager $accessManager)
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
        if (!$this->isValidTree($event)) {
            return;
        }

        $event->getForm()->add('permissions', 'pimee_enrich_category_permissions');
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
        if (!$this->isValidTree($event)) {
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
        if (!$this->isValidTree($event)) {
            return;
        }

        $form = $event->getForm();
        if ($form->isValid()) {
            $viewRoles = $form->get('permissions')->get('view')->getData();
            $editRoles = $form->get('permissions')->get('edit')->getData();
            $this->accessManager->setAccess($event->getData(), $viewRoles, $editRoles);
        }
    }

    /**
     * Predicate to know form event contains valid tree
     *
     * @param FormEvent $event
     *
     * @return boolean
     */
    protected function isValidTree(FormEvent $event)
    {
        return null !== $event->getData() && null !== $event->getData()->getId();
    }
}
