<?php

declare(strict_types=1);

/*
 * This file is part of the Akeneo PIM Enterprise Edition.
 *
 * (c) 2017 Akeneo SAS (http://www.akeneo.com)
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Akeneo\Pim\Permission\Bundle\EventSubscriber;

use Akeneo\Channel\Infrastructure\Component\Model\LocaleInterface;
use Akeneo\Pim\Enrichment\Component\Category\Model\CategoryInterface;
use Akeneo\Pim\Permission\Bundle\Manager\AttributeGroupAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\CategoryAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\JobProfileAccessManager;
use Akeneo\Pim\Permission\Bundle\Manager\LocaleAccessManager;
use Akeneo\Pim\Permission\Bundle\Persistence\ORM\UserGroup\GetUserGroupsWithDefaultPermission;
use Akeneo\Pim\Structure\Component\Model\AttributeGroupInterface;
use Akeneo\Tool\Component\Batch\Model\JobInstance;
use Akeneo\Tool\Component\StorageUtils\StorageEvents;
use Akeneo\UserManagement\Bundle\Doctrine\ORM\Repository\GroupRepository;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Subscriber responsible for setting default permissions on creation for attribute groups, job instances,
 * product categories and locales.
 *
 * By default, users have permission on all new entities, because they are put in the "All" group. It means that a new entity
 * will be automatically visible/editable in Gowth Edition.
 *
 * However, an App is in a user group that *is not* part of the "All" group. In this case, the permission
 * is defined by the default permission configured in the dedicated App user group.
 *
 * In Growth Edition, App user group has default permission set at true for all type of permissions. This way, a new entity is automatically visible by the App.
 *
 * @author Yohan Blain <yohan.blain@akeneo.com>
 */
class AddDefaultPermissionsSubscriber implements EventSubscriberInterface
{
    /** @var GroupRepository */
    private $groupRepository;

    /** @var AttributeGroupAccessManager */
    private $attributeGroupAccessManager;

    /** @var JobProfileAccessManager */
    private $jobInstanceAccessManager;

    /** @var CategoryAccessManager */
    private $productCategoryAccessManager;

    /** @var LocaleAccessManager */
    private $localeAccessManager;

    private GetUserGroupsWithDefaultPermission $getUserGroupsWithDefaultPermission;

    public function __construct(
        GroupRepository $groupRepository,
        AttributeGroupAccessManager $attributeGroupAccessManager,
        JobProfileAccessManager $jobInstanceAccessManager,
        CategoryAccessManager $productCategoryAccessManager,
        LocaleAccessManager $localeAccessManager,
        GetUserGroupsWithDefaultPermission $getUserGroupsWithDefaultPermission
    ) {
        $this->groupRepository = $groupRepository;
        $this->attributeGroupAccessManager = $attributeGroupAccessManager;
        $this->jobInstanceAccessManager = $jobInstanceAccessManager;
        $this->productCategoryAccessManager = $productCategoryAccessManager;
        $this->localeAccessManager = $localeAccessManager;
        $this->getUserGroupsWithDefaultPermission = $getUserGroupsWithDefaultPermission;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            StorageEvents::POST_SAVE => 'setDefaultPermissions',
        ];
    }

    /**
     * Set the default permissions to the new job instance.
     *
     * @param GenericEvent $event
     */
    public function setDefaultPermissions(GenericEvent $event): void
    {
        if (!$event->hasArgument('is_new') || !$event->getArgument('is_new')) {
            return;
        }

        if ($event->hasArgument('is_installation') && $event->getArgument('is_installation')) {
            return;
        }

        $subject = $event->getSubject();

        if ($subject instanceof AttributeGroupInterface) {
            $this->setAttributeGroupDefaultPermissions($subject);
        } elseif ($subject instanceof JobInstance) {
            $this->setJobInstanceDefaultPermissions($subject);
        } elseif ($subject instanceof CategoryInterface && $subject->isRoot()) {
            $this->setRootCategoryDefaultPermissions($subject);
        } elseif ($subject instanceof CategoryInterface) {
            $this->setCategoryDefaultPermissions($subject);
        } elseif ($subject instanceof LocaleInterface) {
            $this->setLocaleDefaultPermissions($subject);
        }
    }

    private function setAttributeGroupDefaultPermissions(AttributeGroupInterface $subject): void
    {
        $groupsWithDefaultViewPermission = $this->getUserGroupsWithDefaultPermission->execute('attribute_group_view');
        $groupsWithDefaultEditPermission = $this->getUserGroupsWithDefaultPermission->execute('attribute_group_edit');

        $defaultGroup = $this->groupRepository->getDefaultUserGroup();

        if (null !== $defaultGroup) {
            $groupsWithDefaultViewPermission[] = $defaultGroup;
            $groupsWithDefaultEditPermission[] = $defaultGroup;
        }

        if (empty($groupsWithDefaultViewPermission) && empty($groupsWithDefaultEditPermission)) {
            return;
        }

        $this->attributeGroupAccessManager->setAccess(
            $subject,
            $groupsWithDefaultViewPermission,
            $groupsWithDefaultEditPermission
        );
    }

    private function setJobInstanceDefaultPermissions(JobInstance $subject): void
    {
        $defaultGroup = $this->groupRepository->getDefaultUserGroup();

        if (null === $defaultGroup) {
            return;
        }

        $this->jobInstanceAccessManager->setAccess(
            $subject,
            [$defaultGroup],
            [$defaultGroup]
        );
    }

    private function setRootCategoryDefaultPermissions(CategoryInterface $subject): void
    {
        $groupsWithDefaultOwnPermission = $this->getUserGroupsWithDefaultPermission->execute('category_own');
        $groupsWithDefaultEditPermission = $this->getUserGroupsWithDefaultPermission->execute('category_edit');
        $groupsWithDefaultViewPermission = $this->getUserGroupsWithDefaultPermission->execute('category_view');

        $defaultGroup = $this->groupRepository->getDefaultUserGroup();

        if (null !== $defaultGroup) {
            $groupsWithDefaultOwnPermission[] = $defaultGroup;
        }

        $this->productCategoryAccessManager->setAccess(
            $subject,
            $groupsWithDefaultViewPermission,
            $groupsWithDefaultEditPermission,
            $groupsWithDefaultOwnPermission
        );
    }

    private function setCategoryDefaultPermissions(CategoryInterface $subject): void
    {
        $this->productCategoryAccessManager->setAccessLikeParent($subject, ['owner' => true]);
    }

    private function setLocaleDefaultPermissions(LocaleInterface $subject): void
    {
        $groupsWithDefaultViewPermission = $this->getUserGroupsWithDefaultPermission->execute('locale_view');
        $groupsWithDefaultEditPermission = $this->getUserGroupsWithDefaultPermission->execute('locale_edit');

        $defaultGroup = $this->groupRepository->getDefaultUserGroup();

        if (null !== $defaultGroup) {
            $groupsWithDefaultViewPermission[] = $defaultGroup;
            $groupsWithDefaultEditPermission[] = $defaultGroup;
        }

        if (empty($groupsWithDefaultViewPermission) && empty($groupsWithDefaultEditPermission)) {
            return;
        }

        $this->localeAccessManager->setAccess(
            $subject,
            $groupsWithDefaultViewPermission,
            $groupsWithDefaultEditPermission
        );
    }
}
