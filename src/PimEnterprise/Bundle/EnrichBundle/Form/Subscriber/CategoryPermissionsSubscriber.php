<?php

namespace PimEnterprise\Bundle\EnrichBundle\Form\Subscriber;

use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Pim\Bundle\CatalogBundle\Model\CategoryInterface;
use PimEnterprise\Bundle\SecurityBundle\Manager\CategoryAccessManager;

/**
 * Subscriber to manage permissions on categories
 *
 * @author    Julien Janvier <julien.janvier@akeneo.com>
 * @copyright 2014 Akeneo SAS (http://www.akeneo.com)
 */
class CategoryPermissionsSubscriber implements EventSubscriberInterface
{
    /** @var CategoryAccessManager */
    protected $accessManager;

    /** array */
    protected $precedentRoles = ['view' => [], 'edit' => []];

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

        $viewRoles = $this->accessManager->getViewRoles($event->getData());
        $form->get('view')->setData($viewRoles);
        $this->precedentRoles['view'] = ($viewRoles instanceof ArrayCollection) ? $viewRoles->toArray() : $viewRoles;

        $editRoles = $this->accessManager->getEditRoles($event->getData());
        $form->get('edit')->setData($editRoles);
        $this->precedentRoles['edit'] = ($editRoles instanceof ArrayCollection) ? $editRoles->toArray() : $editRoles;
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

            $updateChildren = $form->get('permissions')->get('apply_on_children')->getData();
            if ($updateChildren === true) {
                $this->updateChildren($event->getData(), $viewRoles, $editRoles);
            }
        }
    }

    /**
     * Update children categories
     *
     * @param CategoryInterface     $parent
     * @param array|ArrayCollection $viewRoles
     * @param array|ArrayCollection $editRoles
     */
    protected function updateChildren(CategoryInterface $parent, $viewRoles, $editRoles)
    {
        $currentRoles = [];
        $currentRoles['view'] = ($viewRoles instanceof ArrayCollection) ? $viewRoles->toArray() : $viewRoles;
        $currentRoles['edit'] = ($editRoles instanceof ArrayCollection) ? $editRoles->toArray() : $editRoles;

        $addedViewRoles = array_diff($currentRoles['view'], $this->precedentRoles['view']);
        $addedEditRoles = array_diff($currentRoles['edit'], $this->precedentRoles['edit']);
        $removedViewRoles = array_diff($this->precedentRoles['view'], $currentRoles['view']);
        $removedEditRoles = array_diff($this->precedentRoles['edit'], $currentRoles['edit']);

        $changedRoles = count($addedViewRoles) > 0 || count($addedEditRoles) > 0
            || count($removedViewRoles) > 0 || count($removedEditRoles) > 0;

        if ($changedRoles) {
            $this->accessManager->updateChildrenAccesses(
                $parent,
                $addedViewRoles,
                $addedEditRoles,
                $removedViewRoles,
                $removedEditRoles
            );
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
