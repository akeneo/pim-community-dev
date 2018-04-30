<?php

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2015 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace PimEnterprise\Bundle\WorkflowBundle\Form\EventListener;

use Pim\Bundle\UIBundle\Form\Type\SwitchType;
use Pim\Component\User\Model\UserInterface;
use PimEnterprise\Bundle\SecurityBundle\Entity\Repository\CategoryAccessRepository;
use PimEnterprise\Component\Security\Attributes;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * Subscriber to override additional user fields with regular entity fields
 *
 * @author Willy Mesnage <willy.mesnage@akeneo.com>
 */
class UserPreferencesSubscriber implements EventSubscriberInterface
{
    /** @var CategoryAccessRepository */
    protected $categoryAccessRepo;

    /**
     * @param CategoryAccessRepository $categoryAccessRepo
     */
    public function __construct(CategoryAccessRepository $categoryAccessRepo)
    {
        $this->categoryAccessRepo = $categoryAccessRepo;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData'
        ];
    }

    /**
     * Add field in user type if needed
     *
     * @param FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        $this->updateNotifications($event);
    }

    /**
     * Add fields in the user type form if needed
     *
     * @param FormEvent $event
     */
    protected function updateNotifications(FormEvent $event)
    {
        $user = $event->getData();
        if (!$user instanceof UserInterface) {
            return;
        }

        $form = $event->getForm();
        if ($this->categoryAccessRepo->isOwner($user)) {
            $form->add(
                'proposalsToReviewNotification',
                SwitchType::class,
                [
                    'label'    => 'user.proposals.notifications.to_review',
                    'required' => false,
                ]
            );
        }

        $editableCategories = $this->categoryAccessRepo->getGrantedCategoryCodes($user, Attributes::EDIT_ITEMS);
        $ownedCategories = $this->categoryAccessRepo->getGrantedCategoryCodes($user, Attributes::OWN_PRODUCTS);
        $editableButNotOwned = array_diff($editableCategories, $ownedCategories);
        if (!empty($editableCategories) && !empty($editableButNotOwned)) {
            $form->add(
                'proposalsStateNotification',
                SwitchType::class,
                [
                    'label'    => 'user.proposals.notifications.state',
                    'required' => false,
                ]
            );
        }
    }
}
