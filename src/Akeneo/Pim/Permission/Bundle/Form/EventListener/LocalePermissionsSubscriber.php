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

use Akeneo\Pim\Permission\Bundle\Form\Type\LocalePermissionsType;
use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Akeneo\Platform\Bundle\FeatureFlagBundle\FeatureFlags;
use Akeneo\UserManagement\Component\Model\Group;
use Akeneo\UserManagement\Component\Model\GroupInterface;
use Oro\Bundle\SecurityBundle\SecurityFacade;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber to manage permissions on locales
 *
 * @author Nicolas Dupont <nicolas@akeneo.com>
 *
 * @deprecated Will be removed in 2.1. Should be replaced by a subscriber not relying on form events
 *             (like Akeneo\Pim\Permission\Bundle\EventSubscriber\SavePermissionsSubscriber).
 *             Can be done with TIP-738.
 */
class LocalePermissionsSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private LocaleAccessManager $accessManager,
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

        $event->getForm()->add('permissions', LocalePermissionsType::class);
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
        $form->get('view')->setData($this->accessManager->getViewUserGroups($event->getData()));
        $form->get('edit')->setData($this->accessManager->getEditUserGroups($event->getData()));
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
            $currentViewUserGroups = $this->accessManager->getViewUserGroups($event->getData());
            $currentEditUserGroups = $this->accessManager->getEditUserGroups($event->getData());

            $viewUserGroups = array_merge(
                $form->get('permissions')->get('view')->getData(),
                $this->filterHiddenGroups($currentViewUserGroups),
            );
            $editUserGroups = array_merge(
                $form->get('permissions')->get('edit')->getData(),
                $this->filterHiddenGroups($currentEditUserGroups),
            );
            $this->accessManager->setAccess($event->getData(), $viewUserGroups, $editUserGroups);
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
            && null !== $event->getData()
            && null !== $event->getData()->getId()
            && $this->securityFacade->isGranted('pimee_enrich_locale_edit_permissions');
    }

    /**
     * @param GroupInterface[] $groups
     *
     * @return GroupInterface[]
     */
    private function filterHiddenGroups(array $groups): array
    {
        return array_filter($groups, function ($group) {
            return $group->getType() !== Group::TYPE_DEFAULT;
        });
    }
}
