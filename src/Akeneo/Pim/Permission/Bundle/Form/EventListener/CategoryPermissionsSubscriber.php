<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2014 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\Form\EventListener;

use Akeneo\Category\Infrastructure\Component\Classification\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Form\Type\CategoryPermissionsType;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber to manage permissions on categories
 *
 * @author Julien Janvier <julien.janvier@akeneo.com>
 *
 * @deprecated Will be removed in 2.1. Should be replaced by a subscriber not relying on form events
 *             (like Akeneo\Pim\Permission\Bundle\EventSubscriber\SavePermissionsSubscriber).
 *             Can be done with TIP-741.
 */
class CategoryPermissionsSubscriber implements EventSubscriberInterface
{
    /** @var array store the previous roles to be able to do a diff of added/removed */
    protected $previousRoles = ['view' => [], 'edit' => [], 'own' => []];

    public function __construct(
        private CategoryAccessManager $accessManager,
        private SecurityFacade $securityFacade,
        private FeatureFlags $featureFlags
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
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
        if (!$this->isApplicable($event)) {
            return;
        }

        $event->getForm()->add('permissions', CategoryPermissionsType::class);
    }

    /**
     * Inject existing permissions into the form
     *
     * @param FormEvent $event
     */
    public function postSetData(FormEvent $event)
    {
        if (!$this->isApplicable($event)) {
            return;
        }

        $form = $event->getForm()->get('permissions');

        $viewRoles = $this->accessManager->getViewUserGroups($event->getData());
        $viewRoles = $this->keepOnlyPublicGroups($viewRoles);
        $form->get('view')->setData($viewRoles);
        $this->previousRoles['view'] = ($viewRoles instanceof ArrayCollection) ? $viewRoles->toArray() : $viewRoles;

        $editRoles = $this->accessManager->getEditUserGroups($event->getData());
        $editRoles = $this->keepOnlyPublicGroups($editRoles);
        $form->get('edit')->setData($editRoles);
        $this->previousRoles['edit'] = ($editRoles instanceof ArrayCollection) ? $editRoles->toArray() : $editRoles;

        if (isset($this->previousRoles['own'])) {
            $ownRoles = $this->accessManager->getOwnUserGroups($event->getData());
            $ownRoles = $this->keepOnlyPublicGroups($ownRoles);
            $form->get('own')->setData($ownRoles);
            $this->previousRoles['own'] = ($ownRoles instanceof ArrayCollection) ? $ownRoles->toArray() : $ownRoles;
        }
    }

    /**
     * Persist the permissions defined in the form
     *
     * @param FormEvent $event
     */
    public function postSubmit(FormEvent $event)
    {
        if (!$this->isApplicable($event)) {
            return;
        }

        $form = $event->getForm();
        if ($form->isValid()) {
            $currentViewRoles = $this->accessManager->getViewUserGroups($event->getData());
            $currentEditRoles = $this->accessManager->getEditUserGroups($event->getData());
            $currentOwnRoles = $this->accessManager->getOwnUserGroups($event->getData());

            $hiddenViewRoles = $this->keepOnlyHiddenGroups($currentViewRoles);
            $hiddenEditRoles = $this->keepOnlyHiddenGroups($currentEditRoles);
            $hiddenOwnRoles = $this->keepOnlyHiddenGroups($currentOwnRoles);

            $submittedViewRoles = $form->get('permissions')->get('view')->getData();
            $submittedEditRoles = $form->get('permissions')->get('edit')->getData();
            $submittedOwnRoles = isset($this->previousRoles['own']) ? $form->get('permissions')->get('own')->getData() : [];

            $viewRoles = array_merge($submittedViewRoles, $hiddenViewRoles);
            $editRoles = array_merge($submittedEditRoles, $hiddenEditRoles);
            $ownRoles = array_merge($submittedOwnRoles, $hiddenOwnRoles);

            $this->accessManager->setAccess($event->getData(), $viewRoles, $editRoles, $ownRoles);

            $updateChildren = $form->get('permissions')->get('apply_on_children')->getData();
            if ($updateChildren === true) {
                $this->updateChildren($event->getData(), $viewRoles, $editRoles, $ownRoles);
            }
        }
    }

    /**
     * Update children categories
     *
     * @param CategoryInterface     $parent
     * @param array|ArrayCollection $viewRoles
     * @param array|ArrayCollection $editRoles
     * @param array|ArrayCollection $ownRoles
     */
    protected function updateChildren(CategoryInterface $parent, $viewRoles, $editRoles, $ownRoles)
    {
        $currentRoles = [];
        $currentRoles['view'] = ($viewRoles instanceof ArrayCollection) ? $viewRoles->toArray() : $viewRoles;
        $currentRoles['edit'] = ($editRoles instanceof ArrayCollection) ? $editRoles->toArray() : $editRoles;

        if (isset($this->previousRoles['own'])) {
            $currentRoles['own'] = ($ownRoles instanceof ArrayCollection) ? $ownRoles->toArray() : $ownRoles;
            $addedOwnRoles = array_diff($currentRoles['own'], $this->previousRoles['own']);
            $removedOwnRoles = array_diff($this->previousRoles['own'], $currentRoles['own']);
        } else {
            $addedOwnRoles = [];
            $removedOwnRoles = [];
        }

        $addedViewRoles = array_diff($currentRoles['view'], $this->previousRoles['view']);
        $addedEditRoles = array_diff($currentRoles['edit'], $this->previousRoles['edit']);

        $removedViewRoles = array_diff($this->previousRoles['view'], $currentRoles['view']);
        $removedEditRoles = array_diff($this->previousRoles['edit'], $currentRoles['edit']);

        $changedRoles = count($addedViewRoles) > 0 || count($addedEditRoles) > 0 || count($addedOwnRoles) > 0
            || count($removedViewRoles) > 0 || count($removedEditRoles) > 0 || count($removedOwnRoles) > 0;

        if ($changedRoles) {
            $this->accessManager->updateChildrenAccesses(
                $parent,
                $addedViewRoles,
                $addedEditRoles,
                $addedOwnRoles,
                $removedViewRoles,
                $removedEditRoles,
                $removedOwnRoles
            );
        }
    }

    /**
     * Indicates whether the permissions should be added to the form
     *
     * @param FormEvent $event
     *
     * @return bool
     */
    protected function isApplicable(FormEvent $event)
    {
        return $this->featureFlags->isEnabled('permission')
            &&null !== $event->getData()
            && null !== $event->getData()->getId()
            && $this->securityFacade->isGranted('pimee_enrich_category_edit_permissions');
    }

    /**
     * @param GroupInterface[] $groups
     *
     * @return GroupInterface[]
     */
    private function keepOnlyPublicGroups(array $groups): array
    {
        return array_filter($groups, function ($group) {
            return $group->getType() === Group::TYPE_DEFAULT;
        });
    }

    /**
     * @param GroupInterface[] $groups
     *
     * @return GroupInterface[]
     */
    private function keepOnlyHiddenGroups(array $groups): array
    {
        return array_filter($groups, function ($group) {
            return $group->getType() !== Group::TYPE_DEFAULT;
        });
    }
}
